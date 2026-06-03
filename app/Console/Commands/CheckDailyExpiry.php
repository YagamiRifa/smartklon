<?php

namespace App\Console\Commands;

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

        // Cari barang yang TEPAT kedaluwarsa hari ini (atau H-14 hari ini)
        $newlyExpired = BatchExpiry::with('item')
            ->whereDate('expiry_date', $today)
            ->get();

        if ($newlyExpired->isEmpty()) {
            $this->info('Tidak ada barang baru yang expired hari ini.');
            return;
        }

        // Jika ada, pancarkan (broadcast) Event Reverb untuk setiap barang!
        foreach ($newlyExpired as $batch) {
            // Kita memalsukan seolah-olah sistem melakukan "scan"
            // agar tabel dashboard dan lonceng di web langsung update!
            broadcast(new BatchScanned(
                $batch->batch_code,
                'out', // anggap status peringatan
                $batch->item->nama_barang,
                $batch->item->kode_barang
            ));

            $this->info("Notifikasi Reverb dikirim untuk: {$batch->item->nama_barang}");
        }
    }
}
