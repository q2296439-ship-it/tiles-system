<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Branch;
use App\Models\StockMovement;
use App\Models\Announcement;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
     // =====================
// SHOW PRODUCTS
// =====================
public function index(Request $request)
{
    if (!Auth::check()) {
        return redirect('/login');
    }

    $user = Auth::user();
    $role = strtolower($user->role);

    $query = Product::with('branch');

    if (!in_array($role, ['admin', 'manager', 'audit'])) {
        $query->where('branch_id', $user->branch_id);
    }

    // BRANCH FILTER
    if ($request->filled('branch_id')) {
        $query->where('branch_id', $request->branch_id);
    }

    // SEARCH
    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('size', 'like', "%{$search}%")
                ->orWhere('color', 'like', "%{$search}%");
        });
    }

    $products = $query->latest()->paginate(10)->withQueryString();

    // UNIQUE TOTAL PRODUCTS (NAME + SIZE)
    $totalProducts = Product::when(
            !in_array($role, ['admin', 'manager', 'audit']),
            function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            }
        )
        ->when($request->filled('branch_id'), function ($q) use ($request) {
            $q->where('branch_id', $request->branch_id);
        })
        ->get()
        ->unique(function ($item) {
            return strtolower(trim($item->name)) . '|' . strtolower(trim($item->size));
        })
        ->count();

    $branches = Branch::all();

    // 🔥 ADD THIS (FIX AVAILABLE ITEMS)
    $availableItems = Product::when(
            !in_array($role, ['admin', 'manager', 'audit']),
            function ($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            }
        )
        ->when($request->filled('branch_id'), function ($q) use ($request) {
            $q->where('branch_id', $request->branch_id);
        })
        ->when($request->filled('search'), function ($q) use ($request) {
            $search = strtolower(trim($request->search));
            $q->where(function ($qq) use ($search) {
                $qq->whereRaw('LOWER(TRIM(name)) LIKE ?', ["%{$search}%"])
                   ->orWhereRaw('LOWER(TRIM(sku)) LIKE ?', ["%{$search}%"])
                   ->orWhereRaw('LOWER(TRIM(size)) LIKE ?', ["%{$search}%"])
                   ->orWhereRaw('LOWER(TRIM(color)) LIKE ?', ["%{$search}%"]);
            });
        })
        ->sum('stock');

    return view('products.index', compact(
        'products',
        'totalProducts',
        'branches',
        'availableItems' // 🔥 IMPORTANT
    ));
}
    // =====================
    // ADMIN OVERVIEW STOCK
    // =====================
    public function overviewStock()
    {
        $products = Product::with('branch')
            ->latest()
            ->paginate(20);

        return view('admin.overview-stock', compact('products'));
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
// STORE PRODUCT
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

        // ✅ CHECK DUPLICATE PER BRANCH (FIXED)
        $existing = Product::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($request->name))])
            ->whereRaw('LOWER(TRIM(size)) = ?', [strtolower(trim($request->size))])
            ->where('branch_id', $request->branch_id)
            ->first();

        if ($existing) {
            DB::rollBack();
            return back()->with('error', 'Duplicate product in this branch!');
        }

        // ✅ CREATE PRODUCT
        $product = Product::create([
            'sku' => $request->sku,
            'category' => $request->category,
            'name' => trim($request->name),
            'size' => trim($request->size),
            'color' => $request->color,
            'price' => $request->price,
            'stock' => $request->stock,
            'low_stock_threshold' => $request->low_stock_threshold,
            'branch_id' => $request->branch_id,
        ]);

        // ✅ STOCK MOVEMENT
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
        return back()->with('error', $e->getMessage());
    }

    return redirect('/admin/products')->with('success', 'Product added successfully');
}


// =====================
// UPDATE PRODUCT
// =====================
public function update(Request $request, $id)
{
    $product = Product::findOrFail($id);
    $oldPrice = $product->price;

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

        // ✅ CHECK DUPLICATE (EXCEPT SELF)
        $duplicate = Product::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($request->name))])
            ->whereRaw('LOWER(TRIM(size)) = ?', [strtolower(trim($request->size))])
            ->where('branch_id', $request->branch_id)
            ->where('id', '!=', $product->id)
            ->exists();

        if ($duplicate) {
            DB::rollBack();
            return back()->with('error', 'Duplicate product in this branch!');
        }

        // ✅ UPDATE PRODUCT
        $product->update([
            'sku' => $request->sku,
            'category' => $request->category,
            'name' => trim($request->name),
            'size' => trim($request->size),
            'color' => $request->color,
            'price' => $request->price,
            'stock' => $request->stock,
            'low_stock_threshold' => $request->low_stock_threshold,
            'branch_id' => $request->branch_id,
        ]);

        // ✅ STOCK MOVEMENT
        StockMovement::create([
            'product_id' => $product->id,
            'branch_id' => $request->branch_id,
            'type' => 'IN',
            'quantity' => $request->stock,
            'reason' => 'Product updated',
        ]);

        // ✅ PRICE CHANGE LOG
        if ($oldPrice != $request->price) {
            Announcement::create([
                'title' => 'Pricing Adjustment',
                'message' => 'Product ' . $request->name . ' - ' . $request->size .
                    ' price changed from ₱' . number_format($oldPrice, 2) .
                    ' to ₱' . number_format($request->price, 2),
                'created_by' => auth()->id(),
                'is_active' => 1,
            ]);
        }

        DB::commit();

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }

    return redirect('/admin/products')->with('success', 'Product updated successfully');
}