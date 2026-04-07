<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Branch;
use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $branchId = $request->branch_id;

        $branches = Branch::all();

        // ✅ TOTAL PRODUCTS (branch-based)
        $totalProducts = DB::table('branch_product')
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->distinct('product_id')
            ->count('product_id');

        // ✅ LOW STOCKS (branch-based)
        $lowStocks = DB::table('branch_product')
            ->join('products', 'branch_product.product_id', '=', 'products.id')
            ->join('branches', 'branch_product.branch_id', '=', 'branches.id')
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_product.branch_id', $branchId);
            })
            ->whereColumn('branch_product.stock', '<=', 'products.low_stock_threshold')
            ->select(
                'products.name',
                'branch_product.stock',
                'branches.name as branch_name'
            )
            ->get();

        // ✅ TODAY SALES (FIXED)
        $todaySales = Sale::when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->whereDate('created_at', Carbon::today())
            ->sum('total_amount');

        // ✅ TRANSACTIONS TODAY (FIXED)
        $transactionsToday = Sale::when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->whereDate('created_at', Carbon::today())
            ->count();

        // ✅ GRAPH (LAST 7 DAYS)
        $dates = collect();

        for ($i = 6; $i >= 0; $i--) {
            $dates->push(Carbon::today()->subDays($i)->format('Y-m-d'));
        }

        $salesRaw = Sale::selectRaw('DATE(created_at) as date, SUM(total_amount) as total')
            ->when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->groupBy('date')
            ->pluck('total', 'date');

        $salesData = $dates->mapWithKeys(function ($date) use ($salesRaw) {
            return [$date => $salesRaw[$date] ?? 0];
        });

        // ✅ RECENT SALES
        $recentSales = Sale::when($branchId, function ($q) use ($branchId) {
                $q->where('branch_id', $branchId);
            })
            ->latest()
            ->take(5)
            ->get();

        // ✅ TOP BRANCHES (FIXED GROUPING)
        $topBranches = Sale::join('branches', 'sales.branch_id', '=', 'branches.id')
            ->selectRaw('branches.id, branches.name as branch_name, SUM(total_amount) as total')
            ->groupBy('branches.id', 'branches.name')
            ->orderByDesc('total')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'branches',
            'totalProducts',
            'lowStocks',
            'todaySales',
            'transactionsToday',
            'salesData',
            'recentSales',
            'topBranches'
        ));
    }
}