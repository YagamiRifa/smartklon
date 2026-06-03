<?php

namespace App\Console\Commands;

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
        $warningDate = Carbon::today()->addDays(14); // Tetapkan batas mendekati expired (H-14)

        // Cari barang yang TEPAT kedaluwarsa hari ini ATAU TEPAT H-14 dari sekarang
        $newlyExpired = BatchExpiry::with('item')
            ->where(function ($query) use ($today, $warningDate) {
                $query->whereDate('expiry_date', $today)
                    ->orWhereDate('expiry_date', $warningDate);
            })
            ->get();

        if ($newlyExpired->isEmpty()) {
            $this->info('Tidak ada barang baru yang expired atau mendekati expired hari ini.');
            return;
        }

        // Jika ada, pancarkan (broadcast) Event Reverb untuk setiap barang!
        foreach ($newlyExpired as $batch) {
            // Kita parse tanggalnya agar aman saat dibandingkan
            $expiryDate = Carbon::parse($batch->expiry_date)->startOfDay();

            // Tentukan status: jika tanggal kedaluwarsa sama dengan hari ini (atau kelewatan) = expired
            // Jika lebih dari hari ini (yaitu H-14) = warning
            $status = $expiryDate->lessThanOrEqualTo($today) ? 'expired' : 'warning';
            $pesan = $status === 'expired' ? 'Telah kedaluwarsa hari ini!' : 'Memasuki masa kritis (H-14)!';

            // Pancarkan Notifikasi Global!
            broadcast(new BatchExpiryAlert(
                $batch->batch_code,
                $batch->item->nama_barang,
                $status,
                $pesan
            ));

            $this->info("Alarm Global dikirim untuk: {$batch->item->nama_barang} ({$status})");
        }
    }
}
