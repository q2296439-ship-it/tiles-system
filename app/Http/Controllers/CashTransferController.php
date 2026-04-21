<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashTransfer;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class CashTransferController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $role = strtolower($user->role);

        $branchId = $request->branch_id;

        if ($role === 'cashier') {
            $branchId = $user->branch_id;
        }

        $branches = Branch::orderBy('name')->get();

        $receiverBranches = Branch::query();

        if (!empty($branchId)) {
            $receiverBranches->where('id', '!=', $branchId);
        }

        $receiverBranches = $receiverBranches
            ->orderBy('name')
            ->get();

        $outgoing = CashTransfer::with(['fromBranch', 'toBranch', 'user']);
        $incoming = CashTransfer::with(['fromBranch', 'toBranch', 'user']);

        $totalTodayQuery = CashTransfer::whereDate('transfer_date', date('Y-m-d'))
            ->where('status', 'completed');

        if (!empty($branchId)) {

            $outgoing->where('from_branch_id', $branchId);

            $incoming->where('to_branch_id', $branchId)
                ->where('status', 'pending');

            $totalTodayQuery->where('from_branch_id', $branchId);

        } else {

            $incoming->where('status', 'pending');
        }

        $outgoing = $outgoing->latest()->get();
        $incoming = $incoming->latest()->get();
        $totalToday = $totalTodayQuery->sum('amount');

        return view('cashflow.transfer-cash', compact(
            'role',
            'branchId',
            'branches',
            'receiverBranches',
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

        $user = Auth::user();
        $role = strtolower($user->role);

        $fromBranchId = $user->branch_id;

        if ($role !== 'cashier') {
            $fromBranchId = $request->branch_id;
        }

        if (!$fromBranchId) {
            return back()->with('error', 'Please select source branch.');
        }

        CashTransfer::create([
            'from_branch_id' => $fromBranchId,
            'to_branch_id'   => $request->to_branch_id,
            'transfer_date'  => $request->transfer_date,
            'amount'         => $request->amount,
            'notes'          => $request->notes,
            'status'         => 'pending',
            'created_by'     => Auth::id(),
        ]);

        return back()->with('success', 'Transfer request sent successfully.');
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

        return back()->with('success', 'Incoming transfer accepted successfully.');
    }
}