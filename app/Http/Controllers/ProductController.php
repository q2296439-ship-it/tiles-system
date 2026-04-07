<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Branch;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    // =====================
    // SHOW PRODUCTS
    // =====================
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $products = Product::latest()->paginate(10);
        } else {
            $products = Product::where('branch_id', $user->branch_id)
                ->latest()
                ->paginate(10);
        }

        return view('products.index', compact('products'));
    }

    // =====================
    // CREATE FORM
    // =====================
    public function create()
    {
        $branches = Branch::all();
        return view('products.create', compact('branches'));
    }

    // =====================
    // STORE PRODUCT 🔥 FIXED
    // =====================
    public function store(Request $request)
    {
        $request->validate([
            'sku' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'size' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:100',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer',
            'branch_id' => 'required|exists:branches,id',
        ]);

        DB::beginTransaction();

        try {
            // ✅ create product (NO STOCK HERE)
            $product = Product::create([
                'sku' => $request->sku,
                'category' => $request->category,
                'name' => $request->name,
                'size' => $request->size,
                'color' => $request->color,
                'price' => $request->price,
                'low_stock_threshold' => $request->low_stock_threshold,
                'branch_id' => $request->branch_id,
            ]);

            // ✅ insert sa branch_product
            DB::table('branch_product')->insert([
                'product_id' => $product->id,
                'branch_id' => $request->branch_id,
                'stock' => $request->stock,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // ✅ movement log
            StockMovement::create([
                'product_id' => $product->id,
                'branch_id' => $request->branch_id,
                'type' => 'IN',
                'quantity' => $request->stock,
                'reason' => 'Initial stock',
            ]);

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error saving product');
        }

        return redirect('/admin/products')->with('success', 'Product added successfully');
    }

    // =====================
    // EDIT PRODUCT
    // =====================
    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $branches = Branch::all();

        return view('products.edit', compact('product', 'branches'));
    }

    // =====================
    // UPDATE PRODUCT 🔥 FIXED
    // =====================
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'sku' => 'required|string|max:100',
            'category' => 'required|string|max:100',
            'name' => 'required|string|max:255',
            'size' => 'nullable|string|max:100',
            'color' => 'nullable|string|max:100',
            'price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer',
        ]);

        DB::beginTransaction();

        try {
            // update product info
            $product->update([
                'sku' => $request->sku,
                'category' => $request->category,
                'name' => $request->name,
                'size' => $request->size,
                'color' => $request->color,
                'price' => $request->price,
                'low_stock_threshold' => $request->low_stock_threshold,
            ]);

            // 🔥 update branch stock
            $existing = DB::table('branch_product')
                ->where('product_id', $product->id)
                ->where('branch_id', $product->branch_id)
                ->first();

            $oldStock = $existing ? $existing->stock : 0;
            $newStock = $request->stock;
            $diff = $newStock - $oldStock;

            if ($existing) {
                DB::table('branch_product')
                    ->where('product_id', $product->id)
                    ->where('branch_id', $product->branch_id)
                    ->update([
                        'stock' => $newStock,
                        'updated_at' => now(),
                    ]);
            }

            // 🔥 movement log
            if ($diff != 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'branch_id' => $product->branch_id,
                    'type' => 'ADJUST',
                    'quantity' => $diff,
                    'reason' => 'Manual update',
                ]);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating product');
        }

        return redirect('/admin/products')->with('success', 'Product updated successfully');
    }

    // =====================
    // DELETE PRODUCT
    // =====================
    public function delete($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return redirect('/admin/products')->with('success', 'Product deleted successfully');
    }

    // =====================
    // EXPORT CSV (UPDATED 🔥)
    // =====================
    public function export()
    {
        $products = Product::with('branch')->get();

        $filename = "products_export_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Name','Branch','Price']);

            foreach ($products as $p) {
                fputcsv($file, [
                    $p->name,
                    optional($p->branch)->name ?? '-',
                    $p->price,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}