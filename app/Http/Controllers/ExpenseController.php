<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = strtolower($user->role);

        $branchId = $request->branch_id;

        if ($role === 'cashier') {
            $branchId = $user->branch_id;
        }

        $categories = ExpenseCategory::where('status', 1)
    ->orderBy('name')
    ->get();

        $branches = Branch::orderBy('name')->get();

        $expenses = Expense::with(['category', 'user', 'branch']);

        $totalTodayQuery = Expense::whereDate('expense_date', date('Y-m-d'));

        if (!empty($branchId)) {
            $expenses->where('branch_id', $branchId);
            $totalTodayQuery->where('branch_id', $branchId);
        }

        $expenses = $expenses->latest()->get();
        $totalToday = $totalTodayQuery->sum('amount');

        return view('cashflow.expenses', compact(
            'role',
            'branchId',
            'branches',
            'categories',
            'expenses',
            'totalToday'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'expense_date'   => 'required|date',
            'category_id'    => 'required',
            'amount'         => 'required|numeric|min:0.01',
            'payment_method' => 'required',
        ]);

        $user = Auth::user();
        $role = strtolower($user->role);

        $branchId = $user->branch_id;

        if ($role !== 'cashier') {
            $branchId = $request->branch_id;
        }

        Expense::create([
            'branch_id'      => $branchId,
            'category_id'    => $request->category_id,
            'expense_date'   => $request->expense_date,
            'description'    => $request->description,
            'amount'         => $request->amount,
            'payment_method' => $request->payment_method,
            'status'         => 'approved',
            'created_by'     => Auth::id(),
        ]);

        return back()->with('success', 'Expense saved successfully.');
    }

    public function list(Request $request)
    {
        return $this->index($request);
    }

    public function excel(Request $request)
    {
        $user = Auth::user();
        $role = strtolower($user->role);

        $branchId = $request->branch_id;

        if ($role === 'cashier') {
            $branchId = $user->branch_id;
        }

        $expenses = Expense::with(['category', 'user', 'branch']);

        if (!empty($branchId)) {
            $expenses->where('branch_id', $branchId);
        }

        $expenses = $expenses->latest()->get();

        $filename = 'expenses_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($expenses) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Date',
                'Branch',
                'Category',
                'Description',
                'Encoded By',
                'Payment Method',
                'Amount'
            ]);

            foreach ($expenses as $row) {
                fputcsv($file, [
                    $row->expense_date,
                    $row->branch->name ?? '',
                    $row->category->name ?? '',
                    $row->description,
                    $row->user->name ?? '',
                    strtoupper($row->payment_method),
                    $row->amount
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}