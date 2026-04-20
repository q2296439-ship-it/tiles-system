<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashTransfer;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class CashTransferController extends Controller
{
    public function index()
    {
        $branchId = Auth::user()->branch_id;

        $branches = Branch::where('id', '!=', $branchId)
            ->orderBy('name')
            ->get();

        $outgoing = CashTransfer::with(['fromBranch', 'toBranch', 'user'])
            ->where('from_branch_id', $branchId)
            ->latest()
            ->get();

        $incoming = CashTransfer::with(['fromBranch', 'toBranch', 'user'])
            ->where('to_branch_id', $branchId)
            ->where('status', 'pending')
            ->latest()
            ->get();

        $totalToday = CashTransfer::where('from_branch_id', $branchId)
            ->whereDate('transfer_date', date('Y-m-d'))
            ->where('status', 'completed')
            ->sum('amount');

        return view('cashflow.transfer-cash', compact(
            'branches',
            'outgoing',
            'incoming',
            'totalToday'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'to_branch_id'  => 'required',
            'transfer_date' => 'required|date',
            'amount'        => 'required|numeric|min:1',
        ]);

        CashTransfer::create([
            'from_branch_id' => Auth::user()->branch_id,
            'to_branch_id'   => $request->to_branch_id,
            'transfer_date'  => $request->transfer_date,
            'amount'         => $request->amount,
            'notes'          => $request->notes,
            'status'         => 'pending',
            'created_by'     => Auth::id(),
        ]);

        return redirect()
            ->route('cashier.transfer.cash')
            ->with('success', 'Transfer request sent successfully.');
    }

    public function accept($id)
    {
        $transfer = CashTransfer::where('id', $id)
            ->where('to_branch_id', Auth::user()->branch_id)
            ->where('status', 'pending')
            ->firstOrFail();

        $transfer->update([
            'status' => 'completed'
        ]);

        return redirect()
            ->route('cashier.transfer.cash')
            ->with('success', 'Incoming transfer accepted successfully.');
    }
}