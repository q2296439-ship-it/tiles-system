<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashierController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $branchId = $user->branch_id;
        $today = now()->toDateString();

        // ✅ sariling branch lang products
        $products = Product::where('branch_id', $branchId)->get();

        // ✅ Dashboard Stats
        $todaySales = Sale::whereDate('created_at', $today)
            ->where('branch_id', $branchId)
            ->sum('total_amount');

        $receiptCount = \App\Models\Collection::whereDate('receipt_date', $today)
            ->where('branch_id', $branchId)
            ->count();

        $lowStocks = Product::where('branch_id', $branchId)
            ->where('stock', '<=', 5)
            ->orderBy('stock', 'asc')
            ->take(5)
            ->get();

        // ✅ UPDATED: complete recent sales details from collections
        $recentSales = \App\Models\Collection::where('branch_id', $branchId)
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

    // 🔥 CHECKOUT (REALTIME FIXED + SAFE)
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

            // ✅ VALIDATION
            $request->validate([
                'total' => 'required|numeric|min:0',
                'items' => 'required|array|min:1'
            ]);

            DB::beginTransaction();

            // ✅ CREATE SALE
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