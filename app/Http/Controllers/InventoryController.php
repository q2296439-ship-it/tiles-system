<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    // =====================
    // ADD STOCK PAGE 🔥
    // =====================
    public function create()
    {
        $products = Product::all();
        $branches = Branch::all();

        return view('inventory.add_stock', compact('products', 'branches'));
    }

    // =====================
    // STORE STOCK 🔥
    // =====================
    public function store(Request $request)
    {
        if ($request->new_name) {

            $request->validate([
                'new_name' => 'required|string',
                'new_price' => 'required|numeric',
                'quantity' => 'required|integer|min:1',
                'branch_id' => 'required|exists:branches,id'
            ]);

            $product = Product::create([
                'name' => $request->new_name,
                'size' => $request->new_size,
                'price' => $request->new_price,
                'stock' => $request->quantity,
                'color' => 'N/A',
            ]);

            StockMovement::create([
                'product_id' => $product->id,
                'branch_id' => $request->branch_id,
                'type' => 'IN',
                'quantity' => $request->quantity,
                'reason' => 'New Product Added',
            ]);

        } else {

            $request->validate([
                'product_id' => 'required|exists:products,id',
                'quantity' => 'required|integer|min:1',
                'branch_id' => 'required|exists:branches,id'
            ]);

            $product = Product::findOrFail($request->product_id);

            $product->stock += $request->quantity;
            $product->save();

            StockMovement::create([
                'product_id' => $product->id,
                'branch_id' => $request->branch_id,
                'type' => 'IN',
                'quantity' => $request->quantity,
                'reason' => 'Manual Add',
            ]);
        }

        return back()->with('success', 'Saved successfully!');
    }

    // =====================
    // 🔥 CASHIER: TRANSFER IN FORM
    // =====================
    public function transferInForm()
    {
        $products = Product::all();
        $branches = Branch::where('id', '!=', auth()->user()->branch_id)->get();

        $requests = StockMovement::with(['product','branch','from_branch'])
            ->where('type', 'IN_REQUEST')
            ->latest()
            ->get();

        return view('cashier.transferin_cashier', compact('products', 'branches', 'requests'));
    }

    // =====================
    // 🔥 STORE TRANSFER IN
    // =====================
    public function transferInStore(Request $request)
    {
        if (empty($request->items)) {
            return back()->with('error', 'No items selected');
        }

        $request->validate([
            'from_branch_id' => 'required|exists:branches,id',
        ]);

        foreach ($request->items as $item) {

            StockMovement::create([
                'product_id' => $item['product_id'],
                'branch_id' => auth()->user()->branch_id,
                'type' => 'IN_REQUEST',
                'quantity' => $item['qty'],
                'reason' => 'Transfer IN Request',
                'status' => 'pending',
                'requested_by' => auth()->id(),
                'from_branch_id' => $request->from_branch_id,
            ]);
        }

        return back()->with('success', 'Request sent to manager!');
    }

    // =====================
    // 🔥 ADMIN TRANSFER OUT
    // =====================
    public function transferOutAdmin()
    {
        $transfers = StockMovement::with(['product','branch','requester','approver'])
            ->where('type', 'OUT')
            ->latest()
            ->get();

        return view('admin.inventory.transfer-out', compact('transfers'));
    }

    // =====================
    // 🔥 ADMIN TRANSFER IN
    // =====================
    public function transferInAdmin()
    {
        $transfers = StockMovement::with(['product','branch','requester','approver'])
            ->where('type', 'IN_REQUEST')
            ->latest()
            ->get();

        return view('admin.inventory.transfer-in', compact('transfers'));
    }

    // =====================
    // 🔥 TRANSFER OUT FORM
    // =====================
    public function transferOutForm()
    {
        $products = Product::all();
        $branches = Branch::all();

        return view('inventory.transfer_out', compact('products', 'branches'));
    }

    // =====================
    // 🔥 STORE TRANSFER OUT
    // =====================
    public function transferOutStore(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|different:from_branch_id|exists:branches,id',
            'quantity' => 'required|integer|min:1',
        ]);

        StockMovement::create([
            'product_id' => $request->product_id,
            'branch_id' => $request->from_branch_id,
            'type' => 'OUT',
            'quantity' => $request->quantity,
            'reason' => 'Transfer Request',
            'status' => 'pending',
            'requested_by' => auth()->id(),
        ]);

        return back()->with('success', 'Transfer request submitted!');
    }

    // =====================
    // 🔥 ACCEPT TRANSFER
    // =====================
    public function acceptTransfer($id)
    {
        $movement = StockMovement::findOrFail($id);

        $product = Product::find($movement->product_id);
        $product->stock += $movement->quantity;
        $product->save();

        $movement->status = 'completed';
        $movement->save();

        return back()->with('success', 'Transfer accepted!');
    }

    // =====================
    // 🔥 MANAGER DASHBOARD (NEW)
    // =====================
    public function managerDashboard()
    {
        $requests = StockMovement::with(['product','branch','from_branch'])
            ->where('type', 'IN_REQUEST')
            ->where('status', 'pending')
            ->latest()
            ->get();

        $todaySales = 0;
        $monthlySales = 0;
        $totalOrders = 0;
        $lowStockCount = Product::where('stock', '<=', 10)->count();

        return view('manager.dashboard', compact(
            'requests',
            'todaySales',
            'monthlySales',
            'totalOrders',
            'lowStockCount'
        ));
    }

    // =====================
    // 🔥 APPROVAL PAGE (UPDATED FLOW)
    // =====================
    public function approvals()
{
    $branchId = auth()->user()->branch_id;

    $requests = StockMovement::with(['product','branch','from_branch'])
        ->where('type', 'IN_REQUEST')
        ->where(function($query) use ($branchId) {

            // FIRST APPROVAL (receiver - nag request)
            $query->where(function($q) use ($branchId) {
                $q->where('branch_id', $branchId)
                  ->where('status', 'pending');
            });

            // SECOND APPROVAL (sender - magbibigay)
            $query->orWhere(function($q) use ($branchId) {
                $q->where('from_branch_id', $branchId)
                  ->where('status', 'approved_receiver');
            });

        })
        ->latest()
        ->get();

    return view('manager.approvals', compact('requests'));
}

    // =====================
    // 🔥 APPROVE (DUAL FLOW)
    // =====================
    public function approve($id)
{
    $movement = StockMovement::findOrFail($id);

    // STEP 1: Manager ng RECEIVER (San Isidro)
    if ($movement->status == 'pending') {

        $movement->status = 'approved_receiver';

    }
    // STEP 2: Manager ng SENDER (Arayat)
    elseif ($movement->status == 'approved_receiver') {

        $movement->status = 'approved_sender';

    }

    $movement->approved_by = auth()->id();
    $movement->approved_at = now();
    $movement->save();

    return back()->with('success', 'Approval updated!');
}

    // =====================
    // 🔥 REJECT
    // =====================
    public function reject($id)
    {
        $movement = StockMovement::findOrFail($id);

        $movement->status = 'rejected';
        $movement->save();

        return back()->with('success', 'Request rejected!');
    }

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
    // GET MOVEMENTS
    // =====================
    private function getMovements(Request $request)
    {
        $user = Auth::user();

        if (!$user) return collect();

        $query = StockMovement::with(['product', 'branch']);

        if ($user->role !== 'admin') {
            $query->where('branch_id', $user->branch_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        return $query->latest()->limit(20)->get();
    }

    // =====================
    // INVENTORY PAGE
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
    // EXPORT INVENTORY
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
    // EXPORT MOVEMENTS
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
    // TRANSFER (OLD)
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

        $product->decrement('stock', $request->qty);
        $product->increment('stock', $request->qty);

        StockMovement::create([
            'product_id' => $product->id,
            'branch_id' => $request->from_branch,
            'type' => 'OUT',
            'quantity' => -$request->qty,
            'reason' => 'Transfer OUT',
        ]);

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