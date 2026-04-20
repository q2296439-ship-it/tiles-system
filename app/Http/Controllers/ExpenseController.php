<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use Illuminate\Support\Facades\Auth;

class ExpenseController extends Controller
{
    public function index()
    {
        $branchId = Auth::user()->branch_id;

        $categories = ExpenseCategory::where('status', 'active')
            ->orderBy('name')
            ->get();

        $expenses = Expense::with('category')
            ->where('branch_id', $branchId)
            ->latest()
            ->get();

        $totalToday = Expense::where('branch_id', $branchId)
            ->whereDate('expense_date', date('Y-m-d'))
            ->sum('amount');

        return view('cashflow.expenses', compact(
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

        Expense::create([
            'branch_id'      => Auth::user()->branch_id,
            'category_id'    => $request->category_id,
            'expense_date'   => $request->expense_date,
            'description'    => $request->description,
            'amount'         => $request->amount,
            'payment_method' => $request->payment_method,
            'status'         => 'approved',
            'created_by'     => Auth::id(),
        ]);

        return redirect()
            ->route('cashier.expenses')
            ->with('success', 'Expense saved successfully.');
    }

    public function list()
    {
        $branchId = Auth::user()->branch_id;

        $expenses = Expense::with('category')
            ->where('branch_id', $branchId)
            ->latest()
            ->get();

        $totalToday = Expense::where('branch_id', $branchId)
            ->whereDate('expense_date', date('Y-m-d'))
            ->sum('amount');

        return view('cashflow.expense-list', compact(
            'expenses',
            'totalToday'
        ));
    }

    public function excel()
    {
        $expenses = Expense::with('category')
            ->where('branch_id', Auth::user()->branch_id)
            ->latest()
            ->get();

        $filename = 'expenses_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($expenses) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Date',
                'Category',
                'Description',
                'Payment Method',
                'Amount'
            ]);

            foreach ($expenses as $row) {
                fputcsv($file, [
                    $row->expense_date,
                    $row->category->name ?? '',
                    $row->description,
                    strtoupper($row->payment_method),
                    $row->amount
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}