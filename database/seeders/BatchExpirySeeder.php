<?php

namespace Database\Seeders;

use App\Models\BatchExpiry;
use App\Models\Item;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BatchExpirySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Tentukan ID produk yang ingin dikecualikan (misal: ID 5)
        $excludedItemId = 9;

        // Ambil semua ID produk KECUALI yang dikecualikan
        $validItemIds = Item::where('id', '!=', $excludedItemId)->pluck('id');

        // Fungsi penimpa agar factory hanya memilih dari ID yang valid
        $overrideItemId = function () use ($validItemIds) {
            return ['item_id' => $validItemIds->random()];
        };

        // 1. Bikin 30 data normal (masih lama expired-nya)
        BatchExpiry::factory()->count(5)->create();

        // 2. Bikin 10 data yang H-7 kedaluwarsa
        BatchExpiry::factory()->count(7)->hampirExpired()->create();

        // 3. Bikin 10 data yang SUDAH kedaluwarsa
        BatchExpiry::factory()->count(5)->sudahExpired()->create();
    }
}
