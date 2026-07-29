<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\BatchExpiry;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BatchExpiry>
 */
class BatchExpiryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::inRandomOrder()->first()?->id ?? Item::factory(),
            // Default: Masih lama kedaluwarsanya (1 bulan - 2 tahun ke depan)
            'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+2 years')->format('Y-m-d'),
        ];
    }

    /**
     * State khusus untuk data yang hampir kedaluwarsa (H-7 dari hari ini)
     */
    public function hampirExpired(): static
    {
        return $this->state(fn(array $attributes) => [
            'expiry_date' => now()->addDays(7)->format('Y-m-d'),
        ]);
    }

    /**
     * State khusus untuk data yang sudah kedaluwarsa (lewat dari hari ini)
     */
    public function sudahExpired(): static
    {
        return $this->state(fn(array $attributes) => [
            // Mengambil tanggal acak antara 1 tahun yang lalu sampai 1 hari yang lalu
            'expiry_date' => $this->faker->dateTimeBetween('-1 year', '-1 day')->format('Y-m-d'),
        ]);
    }
}
