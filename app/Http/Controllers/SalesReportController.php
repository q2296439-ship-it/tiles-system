<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

// 🔥 EXCEL
use App\Exports\SalesExport;
use App\Exports\BranchSalesExport;
use Maatwebsite\Excel\Facades\Excel;

class SalesReportController extends Controller
{
    // =====================
    // DAILY / WEEKLY / MONTHLY 🔥
    // =====================
    public function daily(Request $request)
    {
        $range = $request->range ?? 'daily';

        if ($request->start_date && $request->end_date) {
            $start = $request->start_date . ' 00:00:00';
            $end = $request->end_date . ' 23:59:59';
        } else {
            if ($range == 'week') {
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
            } elseif ($range == 'month') {
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
            } else {
                $start = now()->startOfDay();
                $end = now()->endOfDay();
            }
        }

        $sales = Sale::with(['branch','user'])
            ->whereBetween('created_at', [$start, $end])
            ->when($request->branch_id, function($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $total = $sales->sum('total_amount');
        $transactionCount = $sales->count();
        $average = $transactionCount > 0 ? $total / $transactionCount : 0;

        // ✅ FIXED FOR POSTGRESQL
        if ($range == 'daily') {
            $trend = Sale::select(
                    DB::raw('EXTRACT(HOUR FROM created_at) as label'),
                    DB::raw('SUM(total_amount) as total')
                )
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        } else {
            $trend = Sale::select(
                    DB::raw("DATE_TRUNC('day', created_at) as label"),
                    DB::raw('SUM(total_amount) as total')
                )
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        }

        $labels = $trend->pluck('label');
        $data = $trend->pluck('total');

        $peak = $trend->sortByDesc('total')->first();

        $alerts = [];

        if ($total == 0) $alerts[] = "❌ No sales data";
        if ($total < 1000) $alerts[] = "⚠ Low sales";
        if ($peak) $alerts[] = "🔥 Peak: {$peak->label}";

        $branchList = DB::table('branches')->get();

        return view('admin.reports.daily', compact(
            'sales','total','transactionCount','average',
            'labels','data','range','alerts','branchList'
        ));
    }

    // =====================
    // 🔥 DAILY PDF EXPORT
    // =====================
    public function exportDailyPdf(Request $request)
    {
        $range = $request->range ?? 'daily';

        if ($request->start_date && $request->end_date) {
            $start = $request->start_date . ' 00:00:00';
            $end = $request->end_date . ' 23:59:59';
        } else {
            if ($range == 'week') {
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
            } elseif ($range == 'month') {
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
            } else {
                $start = now()->startOfDay();
                $end = now()->endOfDay();
            }
        }

        $sales = Sale::with(['branch','user'])
            ->whereBetween('created_at', [$start, $end])
            ->when($request->branch_id, function($q) use ($request) {
                $q->where('branch_id', $request->branch_id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        $total = $sales->sum('total_amount');
        $transactionCount = $sales->count();
        $average = $transactionCount > 0 ? $total / $transactionCount : 0;

        $pdf = Pdf::loadView('admin.reports.daily_pdf', compact(
            'sales','total','transactionCount','average','range','start','end','request'
        ));

        return $pdf->stream('daily_report.pdf');
    }

    // =====================
    // 🔥 EXCEL EXPORT
    // =====================
    public function exportExcel(Request $request)
    {
        $range = $request->range ?? 'daily';

        if ($request->start_date && $request->end_date) {
            $start = $request->start_date . ' 00:00:00';
            $end = $request->end_date . ' 23:59:59';
        } else {
            if ($range == 'week') {
                $start = now()->startOfWeek();
                $end = now()->endOfWeek();
            } elseif ($range == 'month') {
                $start = now()->startOfMonth();
                $end = now()->endOfMonth();
            } else {
                $start = now()->startOfDay();
                $end = now()->endOfDay();
            }
        }

        return Excel::download(
            new SalesExport($start, $end, $request->branch_id),
            'sales_report.xlsx'
        );
    }

    // =====================
    // 🔥 BRANCH EXCEL
    // =====================
    public function exportBranchExcel(Request $request)
    {
        $start = $request->start_date 
            ? $request->start_date . ' 00:00:00' 
            : null;

        $end = $request->end_date 
            ? $request->end_date . ' 23:59:59' 
            : null;

        $branchId = $request->branch_id;

        return Excel::download(
            new BranchSalesExport($start, $end, $branchId),
            'branch_sales.xlsx'
        );
    }

    // =====================
    // PER BRANCH 🔥
    // =====================
    public function perBranch(Request $request)
    {
        $range = $request->range ?? 'today';

        if ($request->start_date && $request->end_date) {
            $start = $request->start_date . ' 00:00:00';
            $end = $request->end_date . ' 23:59:59';
        } else {
            [$start, $end] = $this->getDateRange($range);
        }

        $branchId = $request->branch_id;

        $branches = $this->getBranchData($start, $end, $branchId);

        $grandTotal = $branches->sum('total_sales');
        $totalTransactions = $branches->sum('transactions');
        $average = $totalTransactions > 0 ? $grandTotal / $totalTransactions : 0;

        $topBranch = $branches->first();
        $lowestBranch = $branches->last();

        $branches = $this->addPercentages($branches, $grandTotal);

        $chartLabels = $branches->pluck('branch_name');
        $chartData = $branches->pluck('total_sales');

        $trend = Sale::select(
                DB::raw("DATE_TRUNC('day', created_at) as date"),
                DB::raw('SUM(total_amount) as total')
            )
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $trendLabels = $trend->pluck('date');
        $trendData = $trend->pluck('total');

        $alerts = [];

        foreach ($branches as $branch) {
            if ($branch->total_sales == 0) {
                $alerts[] = "❌ {$branch->branch_name} has no sales";
            }
            if ($branch->total_sales < ($grandTotal * 0.1)) {
                $alerts[] = "⚠ {$branch->branch_name} has low sales";
            }
        }

        if ($topBranch) {
            $alerts[] = "🔥 Top Branch: {$topBranch->branch_name}";
        }

        $branchList = DB::table('branches')->get();

        return view('admin.reports.branch', compact(
            'branches','grandTotal','totalTransactions','average',
            'topBranch','lowestBranch',
            'chartLabels','chartData',
            'trendLabels','trendData',
            'range','alerts','branchList'
        ));
    }

    public function branchData(Request $request)
    {
        $range = $request->range ?? 'today';

        [$start, $end] = $this->getDateRange($range);

        $branches = $this->getBranchData($start, $end, $request->branch_id);

        $grandTotal = $branches->sum('total_sales');
        $totalTransactions = $branches->sum('transactions');

        $branches = $this->addPercentages($branches, $grandTotal);

        return response()->json([
            'branches' => $branches,
            'grandTotal' => $grandTotal,
            'totalTransactions' => $totalTransactions,
            'chartLabels' => $branches->pluck('branch_name'),
            'chartData' => $branches->pluck('total_sales'),
        ]);
    }

    private function getDateRange($range)
    {
        if ($range == 'week') {
            return [now()->startOfWeek(), now()->endOfWeek()];
        } elseif ($range == 'month') {
            return [now()->startOfMonth(), now()->endOfMonth()];
        }
        return [now()->startOfDay(), now()->endOfDay()];
    }

    private function getBranchData($start, $end, $branchId = null)
    {
        return Sale::join('branches', 'sales.branch_id', '=', 'branches.id')
            ->select(
                'branches.id as branch_id',
                'branches.name as branch_name',
                DB::raw('SUM(sales.total_amount) as total_sales'),
                DB::raw('COUNT(*) as transactions')
            )
            ->whereBetween('sales.created_at', [$start, $end])
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('sales.branch_id', $branchId);
            })
            ->groupBy('branches.id','branches.name')
            ->orderByDesc('total_sales')
            ->get();
    }

    private function addPercentages($branches, $grandTotal)
    {
        return $branches->map(function ($branch) use ($grandTotal) {
            $branch->percentage = $grandTotal > 0
                ? round(($branch->total_sales / $grandTotal) * 100, 2)
                : 0;
            return $branch;
        });
    }

    public function exportPdf(Request $request)
    {
        if ($request->start_date && $request->end_date) {
            $start = $request->start_date . ' 00:00:00';
            $end = $request->end_date . ' 23:59:59';
        } else {
            $start = now()->startOfDay();
            $end = now()->endOfDay();
        }

        $branchId = $request->branch_id;

        $branches = $this->getBranchData($start, $end, $branchId);
        $grandTotal = $branches->sum('total_sales');

        $branchName = null;
        if ($branchId) {
            $branch = DB::table('branches')->where('id', $branchId)->first();
            $branchName = $branch->name ?? null;
        }

        $pdf = Pdf::loadView('admin.reports.branch_pdf', compact(
            'branches','grandTotal','branchName','request'
        ));

        return $pdf->stream('branch_report.pdf');
    }
}