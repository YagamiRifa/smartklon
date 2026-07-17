<?php

namespace App\Http\Controllers\Api;

use App\Events\BatchScanned;
use App\Http\Controllers\Controller;
use App\Models\BatchExpiry;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ScannerController extends Controller
{

    // Tambahkan fungsi ini di dalam ScannerController
    public function checkBarcode($barcode)
    {
        // Cek apakah barcode terdaftar di database
        $item = Item::where('barcode', $barcode)->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tersebut belum terdaftar'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Produk ditemukan',
            'data' => [
                'barcode' => $item->barcode,
                'nama_barang' => $item->nama_barang
            ]
        ], 200);
    }
    // public function storeFromScanner(Request $request)
    // {
    //     // 1. Validasi input JSON dari Raspi
    //     $request->validate([
    //         'barcode' => 'required|string',
    //         'expiry_date' => 'required|date_format:d/m/Y'
    //     ]);

    //     // 2. Cek apakah barcode terdaftar di database
    //     $item = Item::where('barcode', $request->barcode)->first();

    //     if (!$item) {
    //         // Mengembalikan pesan error ke Raspi jika barang belum ada [cite: 600]
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Barang tersebut belum terdaftar'
    //         ], 404);
    //     }

    //     // 3. Konversi format tanggal (dd/mm/yyyy -> Y-m-d)
    //     $expiryDate = Carbon::createFromFormat('d/m/Y', $request->expiry_date)->format('Y-m-d');

    //     // 4. Cek apakah tanggal yang sama sudah pernah di-scan untuk produk ini
    //     $existingBatch = BatchExpiry::where('item_id', $item->id)
    //         ->where('expiry_date', $expiryDate)
    //         ->first();

    //     if ($existingBatch) {
    //         // Mengembalikan pesan error ke Raspi jika tanggal ganda [cite: 600]
    //         return response()->json([
    //             'success' => false,
    //             'message' => 'Tanggal tersebut sudah terdaftar'
    //         ], 409);
    //     }

    //     // 5. Simpan Batch Baru
    //     $batch = BatchExpiry::create([
    //         'item_id' => $item->id,
    //         'expiry_date' => $expiryDate,
    //     ]);

    //     // -> TAMBAHKAN BARIS INI <-
    //     // Menyiarkan data ke frontend secara real-time!
    //     broadcast(new BatchScanned($item->barcode, $item->nama_barang, $request->expiry_date));

    //     // 6. Mengembalikan sinyal sukses ke Raspi [cite: 599]
    //     return response()->json([
    //         'success' => true,
    //         'message' => 'Batch berhasil ditambahkan',
    //         'data' => [
    //             'barcode' => $item->barcode,
    //             'nama_barang' => $item->nama_barang,
    //             'expiry_date' => $batch->expiry_date,
    //             'batch_code' => $batch->batch_code
    //         ]
    //     ], 201);
    // }

    // NEW
    public function storeFromScanner(Request $request)
    {
        // 1. Validasi input: expiry_date sekarang boleh dikosongkan atau diisi "-"
        $request->validate([
            'barcode' => 'required|string',
            'expiry_date' => 'nullable|string'
        ]);

        // 2. Cek apakah barcode terdaftar di database
        $item = Item::where('barcode', $request->barcode)->first();

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Barang tersebut belum terdaftar'
            ], 404);
        }

        // --- LOGIKA KHUSUS MODE 2 (JIKA SCANNER HANYA INPUT BARCODE) ---
        if (!$request->expiry_date || $request->expiry_date === '-') {
            // Broadcast ke frontend tanpa tanggal (atau beri keterangan khusus)
            broadcast(new BatchScanned($item->barcode, $item->nama_barang, '-'));

            return response()->json([
                'success' => true,
                'message' => 'Barcode berhasil diverifikasi (Mode Keyboard)',
                'data' => [
                    'barcode' => $item->barcode,
                    'nama_barang' => $item->nama_barang,
                    'expiry_date' => null,
                ]
            ], 200);
        }

        // --- LOGIKA MODE 1 (JIKA TANGGAL ADA & VALID) ---
        // Pastikan format tanggal dari Mode 1 sesuai sebelum dikonversi
        try {
            $expiryDate = Carbon::createFromFormat('d/m/Y', $request->expiry_date)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Format tanggal expired tidak valid'
            ], 422);
        }

        // 4. Cek apakah tanggal yang sama sudah pernah di-scan
        $existingBatch = BatchExpiry::where('item_id', $item->id)
            ->where('expiry_date', $expiryDate)
            ->first();

        if ($existingBatch) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal tersebut sudah terdaftar'
            ], 409);
        }

        // 5. Simpan Batch Baru (Hanya untuk Mode 1 yang memiliki tanggal)
        $batch = BatchExpiry::create([
            'item_id' => $item->id,
            'expiry_date' => $expiryDate,
        ]);

        broadcast(new BatchScanned($item->barcode, $item->nama_barang, $request->expiry_date));

        // 6. Mengembalikan sinyal sukses ke Raspi
        return response()->json([
            'success' => true,
            'message' => 'Batch berhasil ditambahkan',
            'data' => [
                'barcode' => $item->barcode,
                'nama_barang' => $item->nama_barang,
                'expiry_date' => $batch->expiry_date,
            ]
        ], 201);
    }
}
