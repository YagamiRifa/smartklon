<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BatchExpiry;
use App\Models\Item;
use Carbon\Carbon;

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
            return $item;
        });

        // Menghitung statistik global untuk Stat Cards (Paling atas)
        $totalBatch   = $items->sum(fn($item) => $item->batchExpiries->count());
        $safeBatch    = $items->sum('safe_count');
        $warningBatch = $items->sum('warning_count');
        $expiredBatch = $items->sum('expired_count');

        return view('expiry.index', compact('items', 'totalBatch', 'safeBatch', 'warningBatch', 'expiredBatch'));
    }

    /**
     * Menyimpan Batch baru (Bisa via Manual / API dari Raspi nanti)
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_id'     => 'required|exists:items,id',
            'expiry_date' => 'required|date',
        ]);

        BatchExpiry::create([
            'item_id'     => $request->item_id,
            'expiry_date' => $request->expiry_date,
        ]);

        return redirect()->back()->with('success', 'Batch baru berhasil didaftarkan.');
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
