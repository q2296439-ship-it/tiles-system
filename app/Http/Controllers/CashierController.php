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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function totalCash()
    {
        $branchId = Auth::user()->branch_id;

        // CASH IN
        $actualDeposit = Deposit::where('branch_id', $branchId)
            ->where('status', 'closed')
            ->sum('actual_amount');

        $arPayments = Collection::where('branch_id', $branchId)
            ->sum('ar_balance');

        $incomingTransfers = CashTransfer::where('to_branch_id', $branchId)
            ->where('status', 'completed')
            ->sum('amount');

        // CASH OUT
        $expenses = Expense::where('branch_id', $branchId)
            ->where('status', 'approved')
            ->sum('amount');

        $outgoingTransfers = CashTransfer::where('from_branch_id', $branchId)
            ->where('status', 'completed')
            ->sum('amount');

        $cashIn = $actualDeposit + $arPayments + $incomingTransfers;
        $cashOut = $expenses + $outgoingTransfers;

        $totalCash = $cashIn - $cashOut;

        return view('cashflow.total-cash', compact(
            'actualDeposit',
            'arPayments',
            'incomingTransfers',
            'expenses',
            'outgoingTransfers',
            'cashIn',
            'cashOut',
            'totalCash'
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
}