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
        $branchId = $request->branch_id;

        // 🔥 TEMP (lahat muna)
        $products = Product::all();

        // 👉 OPTIONAL (branch-based later)
        // $products = Product::where('branch_id', $branchId)->get();

        return view('cashier.dashboard', compact('products'));
    }

    // 🔥 CHECKOUT (FINAL FIXED)
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

            // 🔥 VALIDATION
            $request->validate([
                'total' => 'required|numeric|min:0',
                'items' => 'required|array|min:1'
            ]);

            DB::beginTransaction();

            // 🔥 CREATE SALE
            $sale = Sale::create([
                'user_id' => $user->id,
                'branch_id' => $user->branch_id,
                'total_amount' => $request->total
            ]);

            foreach ($request->items as $item) {

                // 🔥 VALIDATE ITEM STRUCTURE
                if (!isset($item['id'], $item['qty'], $item['price'])) {
                    throw new \Exception('Invalid cart data');
                }

                $product = Product::find($item['id']);

                if (!$product) {
                    throw new \Exception('Product not found');
                }

                // 🔥 CHECK STOCK
                if ($product->stock < $item['qty']) {
                    throw new \Exception('Not enough stock for ' . $product->name);
                }

                // 🔥 SAVE SALE ITEM
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity' => $item['qty'],
                    'price' => $item['price']
                ]);

                // 🔥 DEDUCT STOCK (SAFE)
                $product->stock = $product->stock - $item['qty'];
                $product->save();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Sale completed successfully!'
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