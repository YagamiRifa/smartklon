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
    public function storeFromScanner(Request $request)
    {
        // 1. Validasi input JSON dari Raspi
        $request->validate([
            'barcode' => 'required|string',
            'expiry_date' => 'required|date_format:d/m/Y'
        ]);

        // 2. Cek apakah barcode terdaftar di database
        $item = Item::where('barcode', $request->barcode)->first();

        if (!$item) {
            // Mengembalikan pesan error ke Raspi jika barang belum ada [cite: 600]
            return response()->json([
                'success' => false,
                'message' => 'Barang tersebut belum terdaftar'
            ], 404);
        }

        // 3. Konversi format tanggal (dd/mm/yyyy -> Y-m-d)
        $expiryDate = Carbon::createFromFormat('d/m/Y', $request->expiry_date)->format('Y-m-d');

        // 4. Cek apakah tanggal yang sama sudah pernah di-scan untuk produk ini
        $existingBatch = BatchExpiry::where('item_id', $item->id)
            ->where('expiry_date', $expiryDate)
            ->first();

        if ($existingBatch) {
            // Mengembalikan pesan error ke Raspi jika tanggal ganda [cite: 600]
            return response()->json([
                'success' => false,
                'message' => 'Tanggal tersebut sudah ada'
            ], 409);
        }

        // 5. Simpan Batch Baru
        $batch = BatchExpiry::create([
            'item_id' => $item->id,
            'expiry_date' => $expiryDate,
        ]);

        // -> TAMBAHKAN BARIS INI <-
        // Menyiarkan data ke frontend secara real-time!
        broadcast(new BatchScanned($item->barcode, $request->expiry_date));

        // 6. Mengembalikan sinyal sukses ke Raspi [cite: 599]
        return response()->json([
            'success' => true,
            'message' => 'Batch berhasil ditambahkan',
            'data' => [
                'barcode' => $item->barcode,
                'nama_barang' => $item->nama_barang,
                'expiry_date' => $batch->expiry_date,
                'batch_code' => $batch->batch_code
            ]
        ], 201);
    }
}
