<?php

namespace App\Http\Controllers;

use App\Models\BatchExpiry;
use App\Models\Item;
use App\Models\Tag;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $warningLimit = Carbon::today()->addDays(14);

        $totalItems       = Item::count();
        $totalInStock     = Tag::where('status', 'in_stock')->count();
        $totalOutOfStock  = Tag::where('status', 'out_of_stock')->count();
        $totalTags        = Tag::count();

        $today = today();
        $stockInToday  = Transaction::where('type', 'in')->whereDate('created_at', $today)->count();
        $stockOutToday = Transaction::where('type', 'out')->whereDate('created_at', $today)->count();

        $recentTransactions = Transaction::with(['tag.item'])
            ->latest()
            ->take(10)
            ->get();

        $topItems = Item::withCount([
            'tags as in_stock_count'    => fn($q) => $q->where('status', 'in_stock'),
            'tags as out_stock_count'   => fn($q) => $q->where('status', 'out_of_stock'),
            'tags as total_tags_count',
        ])->orderByDesc('in_stock_count')->take(5)->get();

        // Ambil data batch yang sudah kedaluwarsa ATAU mendekati kedaluwarsa (H-14)
        $criticalBatches = BatchExpiry::with('item')
            ->where('expiry_date', '<=', $warningLimit)
            ->orderBy('expiry_date', 'asc') // Yang paling kritis ditaruh di paling atas
            ->take(4) // Batasi maksimal 4 data agar halaman tidak kepanjangan
            ->get()
            ->map(function ($batch) use ($today) {
                $expiry = Carbon::parse($batch->expiry_date);
                // Tambahkan flag status untuk mempermudah pewarnaan badge di blade
                $batch->is_expired = $expiry->lessThan($today);
                return $batch;
            });

        return view('dashboard', compact(
            'totalItems',
            'totalInStock',
            'totalOutOfStock',
            'totalTags',
            'stockInToday',
            'stockOutToday',
            'recentTransactions',
            'topItems',
            'criticalBatches'
        ));
    }
}
