<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    // =====================
    // GET PRODUCTS
    // =====================
    private function getProducts()
    {
        $user = Auth::user();

        if (!$user) return collect();

        if ($user->role === 'admin') {
            return Product::with('branch')->get();
        }

        return Product::with('branch')
            ->where('branch_id', $user->branch_id)
            ->get();
    }

    // =====================
    // GET MOVEMENTS (WITH FILTER 🔥)
    // =====================
    private function getMovements(Request $request)
    {
        $user = Auth::user();

        if (!$user) return collect();

        $query = StockMovement::with(['product', 'branch']);

        // 🔥 role filter
        if ($user->role !== 'admin') {
            $query->where('branch_id', $user->branch_id);
        }

        // 🔥 TYPE FILTER (IN / OUT / ADJUST)
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // 🔥 BRANCH FILTER
        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        return $query->latest()->limit(20)->get();
    }

    // =====================
    // SHOW INVENTORY
    // =====================
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $products = $this->getProducts();
        $movements = $this->getMovements($request);
        $branches = Branch::all();

        return view('admin.inventory', compact('products', 'movements', 'branches'));
    }

    // =====================
    // EXPORT INVENTORY CSV
    // =====================
    public function export()
    {
        if (!Auth::check()) return redirect('/login');

        $products = $this->getProducts();

        $filename = "inventory_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function () use ($products) {
            $file = fopen('php://output', 'w');

            fputcsv($file, [
                'Product','Branch','Stock','Price','Value','Status','Last Updated'
            ]);

            foreach ($products as $p) {

                $value = $p->stock * $p->price;

                if ($p->stock == 0) $status = 'OUT OF STOCK';
                elseif ($p->stock <= 10) $status = 'LOW STOCK';
                else $status = 'OK';

                fputcsv($file, [
                    $p->name,
                    optional($p->branch)->name ?? '-',
                    $p->stock,
                    $p->price,
                    $value,
                    $status,
                    optional($p->updated_at)->format('Y-m-d H:i:s') ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =====================
    // EXPORT MOVEMENTS 🔥
    // =====================
    public function exportMovements()
    {
        $movements = StockMovement::with(['product','branch'])->get();

        $filename = "movements_" . date('Y-m-d') . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
        ];

        $callback = function () use ($movements) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['Product','Branch','Type','Qty','Reason','Date']);

            foreach ($movements as $m) {
                fputcsv($file, [
                    $m->product->name,
                    optional($m->branch)->name ?? '-',
                    $m->type,
                    $m->quantity,
                    $m->reason,
                    $m->created_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =====================
    // STOCK TRANSFER 🔥
    // =====================
    public function transfer(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_branch' => 'required|exists:branches,id',
            'to_branch' => 'required|exists:branches,id',
            'qty' => 'required|integer|min:1',
        ]);

        $product = Product::findOrFail($request->product_id);

        // bawas sa source
        $product->decrement('stock', $request->qty);

        // dagdag sa destination (basic version)
        $product->increment('stock', $request->qty);

        // 🔥 LOG OUT
        StockMovement::create([
            'product_id' => $product->id,
            'branch_id' => $request->from_branch,
            'type' => 'OUT',
            'quantity' => -$request->qty,
            'reason' => 'Transfer OUT',
        ]);

        // 🔥 LOG IN
        StockMovement::create([
            'product_id' => $product->id,
            'branch_id' => $request->to_branch,
            'type' => 'IN',
            'quantity' => $request->qty,
            'reason' => 'Transfer IN',
        ]);

        return back()->with('success', 'Stock transferred successfully');
    }
}