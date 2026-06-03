<?php

namespace App\Http\Controllers;

use App\Events\BatchExpiryAlert;
use App\Models\BatchExpiry;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ExpiryController extends Controller
{
    /**
     * Menampilkan halaman utama Expiry (Status Kedaluwarsa)
     */
    public function index()
    {
        $today = Carbon::today();
        $warningDate = Carbon::today()->addDays(14); // Batas H-14

        // Mengambil semua item beserta batch-nya
        $items = Item::with('batchExpiries')->get()->map(function ($item) use ($today, $warningDate) {
            $item->safe_count = 0;
            $item->warning_count = 0;
            $item->expired_count = 0;

            // Menghitung status per item
            foreach ($item->batchExpiries as $batch) {
                $expiry = Carbon::parse($batch->expiry_date);
                if ($expiry->lessThan($today)) {
                    $item->expired_count++;
                } elseif ($expiry->between($today, $warningDate)) {
                    $item->warning_count++;
                } else {
                    $item->safe_count++;
                }
            }

            $earliest = $item->batchExpiries->min('expiry_date');
            $item->earliest_date = $earliest ? Carbon::parse($earliest)->format('Y-m-d') : '9999-12-31';
            return $item;
        });

        // Menghitung statistik global untuk Stat Cards (Paling atas)
        $totalBatch   = $items->sum(fn($item) => $item->batchExpiries->count());
        $safeBatch    = $items->sum('safe_count');
        $warningBatch = $items->sum('warning_count');
        $expiredBatch = $items->sum('expired_count');

        // Ambil 10 batch terbaru yang baru saja masuk ke database
        $recentLogs = BatchExpiry::with('item') // Memuat relasi tabel items untuk mengambil barcode
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Pastikan 'recentLogs' ditambahkan ke dalam compact()
        return view('expiry.index', compact('items', 'totalBatch', 'safeBatch', 'warningBatch', 'expiredBatch', 'recentLogs'));
    }

    /**
     * Menyimpan data batch baru dari input manual (Web)
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'expiry_date' => 'required|date',
        ]);

        $batch = BatchExpiry::create([
            'item_id' => $request->item_id,
            'expiry_date' => $request->expiry_date,
        ]);

        $item = Item::find($request->item_id);

        // === 🚀 AWAL TAMBAHAN LOGIKA PENGECEKAN INSTAN ===
        $batch->setRelation('item', $item); // Hubungkan data item ke batch agar terbaca di event

        $today = Carbon::today();
        $expiryDate = Carbon::parse($batch->expiry_date)->startOfDay();
        $warningLimit = $today->copy()->addDays(14); // Batas H-14

        // Cek apakah tanggal yang diinput mepet atau sudah lewat
        if ($expiryDate->lessThanOrEqualTo($warningLimit)) {

            $status = $expiryDate->lessThanOrEqualTo($today) ? 'expired' : 'warning';

            if ($status === 'warning') {
                $sisaHari = $today->diffInDays($expiryDate);
                $pesan = "Baru diinput: Memasuki masa kritis (Sisa {$sisaHari} hari)!";
            } else {
                $pesan = "Baru diinput: Barang ini sudah kedaluwarsa!";
            }

            // Tembakkan Notifikasi Global secara Real-time!
            broadcast(new BatchExpiryAlert(
                $batch->batch_code ?? '-', // Jika batch_code otomatis di-generate DB
                $item->nama_barang,
                $status,
                $pesan
            ));
        }
        // === 🚀 AKHIR TAMBAHAN LOGIKA PENGECEKAN INSTAN ===


        // Jika request datang dari AJAX (JavaScript Fetch)
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Batch item ' . $item->nama_barang . ' berhasil di tambah.',
                'data' => [
                    'barcode' => $item->barcode ?? 'Tanpa Barcode',
                    'nama_barang' => $item->nama_barang, // Tambahan baru
                    'expiry_date' => Carbon::parse($batch->expiry_date)->format('d/m/Y')
                ]
            ]);
        }

        // Fallback jika tidak menggunakan AJAX
        return redirect()->back()->with('success', 'Batch berhasil ditambahkan.');
    }

    /**
     * API: Mengambil daftar batch per item untuk fitur "Expand" pada tabel
     */
    public function getBatches($id)
    {
        $item = Item::with(['batchExpiries' => function ($q) {
            $q->orderBy('expiry_date', 'asc');
        }])->findOrFail($id);

        $today = Carbon::today();
        $warningDate = Carbon::today()->addDays(14);

        // Memformat data menjadi JSON untuk JavaScript
        $batches = $item->batchExpiries->map(function ($batch) use ($today, $warningDate) {
            $expiry = Carbon::parse($batch->expiry_date);

            if ($expiry->lessThan($today)) {
                $status = 'expired';
            } elseif ($expiry->between($today, $warningDate)) {
                $status = 'warning';
            } else {
                $status = 'aman';
            }

            return [
                'id'          => $batch->id,
                'batch_code'  => $batch->batch_code,
                'expiry_date' => $expiry->format('d/m/Y'),
                'status'      => $status
            ];
        });

        return response()->json([
            'success' => true,
            'batches' => $batches
        ]);
    }
    /**
     * API: Menghapus data batch berdasarkan ID
     */
    public function destroyBatch($id)
    {
        try {
            $batch = BatchExpiry::findOrFail($id);
            $batch->delete();

            return response()->json([
                'success' => true,
                'message' => 'Batch berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus batch'
            ], 500);
        }
    }
}
