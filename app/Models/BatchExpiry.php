<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class BatchExpiry extends Model
{
    use HasFactory;
    protected $fillable = ['item_id', 'batch_code', 'expiry_date'];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Kita menggunakan now()->format('Ymd') untuk mendapatkan struktur yyyymmdd
            $date = now()->format('Ymd');
            $prefix = "BTH-{$date}-";

            // Mencari batch terakhir di hari yang sama
            $lastBatch = static::where('batch_code', 'like', "{$prefix}%")
                ->orderBy('id', 'desc')
                ->first();

            if ($lastBatch) {
                // Memotong 4 angka terakhir, menjadikannya integer, lalu ditambah 1
                $lastNumber = (int) Str::substr($lastBatch->batch_code, -4);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            // Memformat menjadi 4 digit (contoh: 0001, 0002)
            $model->batch_code = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    // Relasi balik ke Item
    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
