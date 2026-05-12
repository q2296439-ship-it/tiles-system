<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Collection;
use App\Models\Deposit;
use App\Models\Expense;
use App\Models\CashTransfer;
use App\Models\ArPayment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class CashierController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $today = now()->toDateString();

        $products = Product::where('branch_id', $branchId)->get();

        $todaySales = Sale::whereDate('created_at', $today)
            ->where('branch_id', $branchId)
            ->sum('total_amount');

        $receiptCount = Collection::whereDate('receipt_date', $today)
            ->where('branch_id', $branchId)
            ->count();

        $lowStocks = Product::where('branch_id', $branchId)
            ->where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        $recentSales = Collection::where('branch_id', $branchId)
            ->latest()
            ->take(5)
            ->get();

        return view('cashier.dashboard', compact(
            'products',
            'todaySales',
            'receiptCount',
            'lowStocks',
            'recentSales'
        ));
    }

    public function totalCash(Request $request)
{
    $user = Auth::user();
    $role = strtolower($user->role);

    $branchId = $request->branch_id;

    if ($role === 'cashier') {
        $branchId = $user->branch_id;
    }

    // SELECTED DATE
    $selectedDate = $request->date ?? now()->toDateString();

    $branches = \App\Models\Branch::orderBy('name')->get();

    /*
    |--------------------------------------------------------------------------
    | TOTAL CASH UP TO SELECTED DATE
    |--------------------------------------------------------------------------
    */

    // CASH IN
    $depositQuery = Deposit::where('status', 'closed')
        ->whereDate('deposit_date', '<=', $selectedDate);

    $arQuery = ArPayment::whereDate('payment_date', '<=', $selectedDate);

    $incomingQuery = CashTransfer::where('status', 'completed')
        ->whereDate('transfer_date', '<=', $selectedDate);

    // CASH OUT
    $expenseQuery = Expense::where('status', 'approved')
        ->whereDate('expense_date', '<=', $selectedDate);

    $outgoingQuery = CashTransfer::where('status', 'completed')
        ->whereDate('transfer_date', '<=', $selectedDate);

    if (!empty($branchId)) {

        $depositQuery->where('branch_id', $branchId);
        $arQuery->where('branch_id', $branchId);
        $incomingQuery->where('to_branch_id', $branchId);

        $expenseQuery->where('branch_id', $branchId);
        $outgoingQuery->where('from_branch_id', $branchId);
    }

    $actualDeposit = $depositQuery->sum('actual_amount');
    $arPayments = $arQuery->sum('amount');
    $incomingTransfers = $incomingQuery->sum('amount');

    $expenses = $expenseQuery->sum('amount');
    $outgoingTransfers = $outgoingQuery->sum('amount');

    $cashIn = round(
        $actualDeposit + $arPayments + $incomingTransfers,
        2
    );

    $cashOut = round(
        $expenses + $outgoingTransfers,
        2
    );

    $totalCash = round(
        $cashIn - $cashOut,
        2
    );

    /*
    |--------------------------------------------------------------------------
    | DAILY CASH FLOW (SELECTED DATE ONLY)
    |--------------------------------------------------------------------------
    */

    $todayDepositQuery = Deposit::where('status', 'closed')
        ->whereDate('deposit_date', $selectedDate);

    $todayArQuery = ArPayment::whereDate('payment_date', $selectedDate);

    $todayIncomingQuery = CashTransfer::where('status', 'completed')
        ->whereDate('transfer_date', $selectedDate);

    $todayExpenseQuery = Expense::where('status', 'approved')
        ->whereDate('expense_date', $selectedDate);

    $todayOutgoingQuery = CashTransfer::where('status', 'completed')
        ->whereDate('transfer_date', $selectedDate);

    if (!empty($branchId)) {

        $todayDepositQuery->where('branch_id', $branchId);
        $todayArQuery->where('branch_id', $branchId);
        $todayIncomingQuery->where('to_branch_id', $branchId);

        $todayExpenseQuery->where('branch_id', $branchId);
        $todayOutgoingQuery->where('from_branch_id', $branchId);
    }

    $todayDeposit = $todayDepositQuery->sum('actual_amount');
    $todayArPayments = $todayArQuery->sum('amount');
    $todayIncomingTransfers = $todayIncomingQuery->sum('amount');

    $todayExpenses = $todayExpenseQuery->sum('amount');
    $todayOutgoingTransfers = $todayOutgoingQuery->sum('amount');

    $todayCashIn = round(
        $todayDeposit + $todayArPayments + $todayIncomingTransfers,
        2
    );

    $todayCashOut = round(
        $todayExpenses + $todayOutgoingTransfers,
        2
    );

    $previousBalance = round(
        $totalCash - ($todayCashIn - $todayCashOut),
        2
    );

    return view('cashflow.total-cash', compact(
        'branches',
        'branchId',
        'role',
        'selectedDate',

        'actualDeposit',
        'arPayments',
        'incomingTransfers',
        'expenses',
        'outgoingTransfers',
        'cashIn',
        'cashOut',
        'totalCash',

        'todayDeposit',
        'todayArPayments',
        'todayIncomingTransfers',
        'todayExpenses',
        'todayOutgoingTransfers',
        'todayCashIn',
        'todayCashOut',

        'previousBalance'
    ));
}

    public function checkout(Request $request)
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not authenticated'
                ], 401);
            }

            $request->validate([
                'total' => 'required|numeric|min:0',
                'items' => 'required|array|min:1'
            ]);

            DB::beginTransaction();

            $sale = Sale::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'total_amount' => $request->total
            ]);

            foreach ($request->items as $item) {

                if (!isset($item['id'], $item['qty'], $item['price'])) {
                    throw new \Exception('Invalid cart data');
                }

                $product = Product::find($item['id']);

                if (!$product) {
                    throw new \Exception('Product not found');
                }

                if ($product->branch_id != $user->branch_id) {
                    throw new \Exception('Invalid product branch');
                }

                if ($product->stock < $item['qty']) {
                    throw new \Exception('Not enough stock for ' . $product->name);
                }

                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['qty'],
                    'price' => $item['price']
                ]);

                $product->stock -= $item['qty'];
                $product->save();
            }

            DB::commit();

            $updatedProducts = Product::where('branch_id', $user->branch_id)->get();

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully!',
                'products' => $updatedProducts
            ]);

        } catch (\Exception $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function cashFlowExcel(Request $request)
{
    $user = Auth::user();
    $role = strtolower($user->role);

    $branchId = $request->branch_id;

    if ($role === 'cashier') {
        $branchId = $user->branch_id;
    }

    $selectedDate = $request->date ?? now()->toDateString();

    /*
    |--------------------------------------------------------------------------
    | CASH IN
    |--------------------------------------------------------------------------
    */

    $todayDeposit = Deposit::where('status', 'closed')
        ->whereDate('deposit_date', $selectedDate)
        ->when($branchId, function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
        ->sum('actual_amount');

    $todayArPayments = ArPayment::whereDate('payment_date', $selectedDate)
        ->when($branchId, function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
        ->sum('amount');

    $todayIncomingTransfers = CashTransfer::where('status', 'completed')
        ->whereDate('transfer_date', $selectedDate)
        ->when($branchId, function ($q) use ($branchId) {
            $q->where('to_branch_id', $branchId);
        })
        ->sum('amount');

    /*
    |--------------------------------------------------------------------------
    | CASH OUT
    |--------------------------------------------------------------------------
    */

    $todayExpenses = Expense::where('status', 'approved')
        ->whereDate('expense_date', $selectedDate)
        ->when($branchId, function ($q) use ($branchId) {
            $q->where('branch_id', $branchId);
        })
        ->sum('amount');

    $todayOutgoingTransfers = CashTransfer::where('status', 'completed')
        ->whereDate('transfer_date', $selectedDate)
        ->when($branchId, function ($q) use ($branchId) {
            $q->where('from_branch_id', $branchId);
        })
        ->sum('amount');

    /*
    |--------------------------------------------------------------------------
    | TOTALS
    |--------------------------------------------------------------------------
    */

    $todayCashIn =
        $todayDeposit +
        $todayArPayments +
        $todayIncomingTransfers;

    $todayCashOut =
        $todayExpenses +
        $todayOutgoingTransfers;

    $netCash =
        $todayCashIn -
        $todayCashOut;

    /*
    |--------------------------------------------------------------------------
    | EXPORT ROWS
    |--------------------------------------------------------------------------
    */

    $rows = collect([
        [
            'Description' => 'Actual Deposit',
            'Amount' => $todayDeposit,
        ],
        [
            'Description' => 'A/R Payment',
            'Amount' => $todayArPayments,
        ],
        [
            'Description' => 'Incoming Transfers',
            'Amount' => $todayIncomingTransfers,
        ],
        [
            'Description' => 'Expenses',
            'Amount' => $todayExpenses,
        ],
        [
            'Description' => 'Transfer to Other Branch',
            'Amount' => $todayOutgoingTransfers,
        ],
        [
            'Description' => 'NET CASH',
            'Amount' => $netCash,
        ],
    ]);

    /*
    |--------------------------------------------------------------------------
    | BRANCH NAME
    |--------------------------------------------------------------------------
    */

    $branchName = 'All Branches';

    if (!empty($branchId)) {

        $branch = \App\Models\Branch::find($branchId);

        if ($branch) {
            $branchName = $branch->name;
        }
    }

    /*
    |--------------------------------------------------------------------------
    | EXPORT
    |--------------------------------------------------------------------------
    */

    return Excel::download(
        new \App\Exports\CashFlowExport(
            $rows,
            $selectedDate,
            $branchName
        ),
        'cash-flow-' . $selectedDate . '.xlsx'
    );
}
}