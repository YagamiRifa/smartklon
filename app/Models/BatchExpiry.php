<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BatchExpiry extends Model
{
    protected $fillable = ['batch_code', 'item_id', 'expiry_date',];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // 1. Tentukan prefix sesuai format (BTH-YYYYMMDD-)
            $prefix = 'BTH-' . Carbon::now()->format('Ymd') . '-';

            // 2. Cari data terakhir di database yang memiliki prefix yang sama
            $latestBatch = self::where('batch_code', 'LIKE', $prefix . '%')
                ->latest('id')
                ->first();

            if ($latestBatch) {
                // 3. Jika ada, ambil 4 digit angka terakhir dan tambah 1
                $lastNumber = (int) substr($latestBatch->batch_code, -4);
                $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            } else {
                // 4. Jika belum ada data di hari ini, mulai dari 0001
                $newNumber = '0001';
            }

            // 5. Gabungkan dan masukkan ke atribut model
            $model->batch_code = $prefix . $newNumber;
        });
    }
    // Relasi balik ke Item
    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
