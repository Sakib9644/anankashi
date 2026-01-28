<?php

namespace App\Http\Controllers\Web\Backend;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class DashboardController extends Controller
{
    public function __construct()
    {
        View::share('crud', 'dashboard');
    }

   public function index()
{
    // ===== User Analytics =====
    $all_months = [
        'january',
        'february',
        'march',
        'april',
        'may',
        'june',
        'july',
        'august',
        'september',
        'october',
        'november',
        'december'
    ];

    // Users grouped by month
    $users = \App\Models\User::select(
        DB::raw("MONTH(created_at) as month_number"),
        DB::raw("MIN(MONTHNAME(created_at)) as month_name"),
        DB::raw("COUNT(*) as total")
    )
        ->groupBy(DB::raw('MONTH(created_at)'))
        ->orderBy('month_number')
        ->get()
        ->mapWithKeys(function ($item) {
            return [
                strtolower($item->month_name) => (int) $item->total
            ];
        });

    // Fill missing months with 0
    $userAnalytics = collect($all_months)->mapWithKeys(function ($month) use ($users) {
        return [
            ucfirst($month) => $users->get($month, 0)
        ];
    });

    // ===== Calculate Growth Percentage =====
    $growth = [];
    $previous = null;
    foreach ($userAnalytics as $month => $count) {
        if ($previous === null) {
            $growth[$month] = 0; // First month has no growth
        } else {
            // Percentage growth from previous month
            $growth[$month] = $previous > 0 ? round((($count - $previous) / $previous) * 100, 1) : 0;
        }
        $previous = $count;
    }

    // ===== Latest Users =====
    $recentUsers = \App\Models\User::orderBy('created_at', 'desc')->limit(6)->get();

    // ===== Dashboard summary counts =====
    $totalUsers = \App\Models\User::count();
    $totalTransactions = \App\Models\Transaction::where('status', 'success')->count();
    $totalIncrement = \App\Models\Transaction::where('status', 'success')->where('type', 'increment')->sum('amount');
    $totalDecrement = \App\Models\Transaction::where('status', 'success')->where('type', 'decrement')->sum('amount');

    session()->forget('t-success');

    return view('backend.layouts.dashboard', compact(
        'userAnalytics',
        'growth',          // <-- growth percentages
        'recentUsers',
        'totalUsers',
        'totalTransactions',
        'totalIncrement',
        'totalDecrement'
    ));
}

}
