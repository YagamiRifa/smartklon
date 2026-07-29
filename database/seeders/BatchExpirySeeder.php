<?php

namespace Database\Seeders;

use App\Models\BatchExpiry;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BatchExpirySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Bikin 30 data normal (masih lama expired-nya)
        BatchExpiry::factory()->count(3)->create();

        // 2. Bikin 10 data yang H-7 kedaluwarsa
        BatchExpiry::factory()->count(2)->hampirExpired()->create();

        // 3. Bikin 10 data yang SUDAH kedaluwarsa
        BatchExpiry::factory()->count(1)->sudahExpired()->create();
    }
}
