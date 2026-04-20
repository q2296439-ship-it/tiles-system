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
        $categories = ExpenseCategory::where('status', 'active')->get();
        $expenses = Expense::with('category')->latest()->get();

        return view('cashflow.expenses', compact('categories', 'expenses'));
    }

    public function store(Request $request)
    {
        Expense::create([
            'branch_id' => Auth::user()->branch_id,
            'category_id' => $request->category_id,
            'expense_date' => $request->expense_date,
            'description' => $request->description,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'status' => 'approved',
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Expense added successfully.');
    }
}