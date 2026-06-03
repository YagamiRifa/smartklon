<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Tambahkan use Auth jika ingin menggunakan Facade (opsional jika pakai $request)
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Menandai semua notifikasi user yang login menjadi "Sudah Dibaca"
     */
    // KUNCI 1: Masukkan Request $request ke dalam fungsi
    public function clear(Request $request)
    {
        try {
            // KUNCI 2: Gunakan $request->user() untuk menghindari error "Undefined method"
            if ($request->user()) {
                $request->user()->unreadNotifications->markAsRead();
            }

            return response()->json([
                'success' => true,
                'message' => 'Semua notifikasi berhasil dibersihkan.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membersihkan notifikasi: ' . $e->getMessage()
            ], 500);
        }
    }
}
