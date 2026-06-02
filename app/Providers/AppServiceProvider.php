<?php

namespace App\Providers;

use App\Models\BatchExpiry;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('*', function ($view) {
            // Ambil waktu terakhir kali user membersihkan notifikasi dari session
            $clearedAt = session('notifications_cleared_at');

            $warningQuery = BatchExpiry::where('expiry_date', '<=', Carbon::now()->addDays(14))
                ->where('expiry_date', '>=', Carbon::now());

            $expiredQuery = BatchExpiry::where('expiry_date', '<', Carbon::now());

            // Jika user pernah menekan "Bersihkan", hanya tampilkan data baru yang dibuat SETELAH-nya
            if ($clearedAt) {
                $warningQuery->where('created_at', '>', $clearedAt);
                $expiredQuery->where('created_at', '>', $clearedAt);
            }

            $view->with('globalWarningCount', $warningQuery->count())
                ->with('globalExpiredCount', $expiredQuery->count());
        });
    }
}
