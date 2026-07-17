<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ExpiryDatabaseNotification;
use App\Events\BatchExpiryAlert;
use App\Events\BatchScanned;
use App\Models\BatchExpiry;
use Carbon\Carbon;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:check-daily-expiry')]
#[Description('Command description')]
class CheckDailyExpiry extends Command
{
    /**
     * Execute the console command.
     */
    // Ini nama perintah yang akan kita ketik di terminal nanti
    protected $signature = 'expiry:check-daily';
    protected $description = 'Mengecek barang yang kedaluwarsa hari ini dan mengirim notifikasi Reverb';

    public function handle()
    {
        $today = Carbon::today();
        $warningLimit = Carbon::today()->addDays(7); // Batas maksimal H-7

        // Ambil SEMUA barang yang tanggal kedaluwarsanya <= H-7
        // (Ini mencakup yang expired dan yang mendekati expired)
        $kritis = BatchExpiry::with('item')
            ->whereDate('expiry_date', '<=', $warningLimit)
            ->get();

        if ($kritis->isEmpty()) {
            $this->info('Tidak ada barang baru yang expired atau mendekati expired.');
            return;
        }

        // Jika ada, pancarkan (broadcast) Event Reverb untuk setiap barang!
        foreach ($kritis as $batch) {
            $expiryDate = Carbon::parse($batch->expiry_date)->startOfDay();

            // Jika tanggalnya lewat atau sama dengan hari ini = expired
            // Jika masih di atas hari ini (tapi di bawah H-7) = warning
            $status = $expiryDate->lessThanOrEqualTo($today) ? 'expired' : 'warning';

            // Hitung sisa hari untuk ditampilkan di pesan
            if ($status === 'warning') {
                $sisaHari = $today->diffInDays($expiryDate);
                $pesan = "Memasuki masa kritis (Sisa {$sisaHari} hari)!";
            } else {
                $pesan = "Telah kedaluwarsa!";
            }

            // Pancarkan Notifikasi Global!
            broadcast(new BatchExpiryAlert(
                $batch->batch_code,
                $batch->item->nama_barang,
                $status,
                $pesan
            ));
            // Ambil semua user (Admin)
            $admins = User::all();

            // Simpan riwayat notifikasi ke Database untuk semua admin
            Notification::send($admins, new ExpiryDatabaseNotification(
                $batch->batch_code ?? '-',
                $item->nama_barang ?? 'Produk Tidak Diketahui',
                $status,
                $pesan
            ));

            $this->info("Alarm Global dikirim untuk: {$batch->item->nama_barang} ({$status})");
        }
    }
}
