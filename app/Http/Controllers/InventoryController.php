<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Branch;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;

use App\Exports\InventoryExport;
use App\Exports\TransferInExport;
use App\Exports\TransferOutExport;
use App\Models\DefectiveStock;

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
    DB::beginTransaction();

    try {

    if ($request->mode === 'new') {

    $request->validate([
        'new_name'  => 'required|string',
        'new_price' => 'nullable|numeric',
        'quantity'  => 'required|integer|min:1',
        'branch_id' => 'required|exists:branches,id'
    ]);

    if (strtolower(auth()->user()->role) === 'cashier') {
        $request->merge([
            'new_price' => 0
        ]);
    }

    $name  = trim($request->new_name);
    $size  = trim($request->new_size);
    $price = $request->new_price;

    $exists = Product::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
        ->whereRaw('LOWER(TRIM(size)) = ?', [strtolower($size)])
        ->where('branch_id', $request->branch_id)
        ->exists();

    if ($exists) {
        throw new \Exception('Product already existed on the system.');
    }

    $product = Product::create([
        'name'      => $name,
        'size'      => $size,
        'price'     => $price,
        'stock'     => $request->quantity,
        'color'     => 'N/A',
        'branch_id' => $request->branch_id,
    ]);

    StockMovement::create([
        'product_id' => $product->id,
        'branch_id'  => $request->branch_id,
        'type'       => 'IN',
        'quantity'   => $request->quantity,
        'reason'     => 'New Product Added',
        'dr_number'  => $request->dr_number_new,
        'unit_price' => $price,
    ]);

    // AUTO CENTRALIZED IF SAN ISIDRO
    if ((int)$request->branch_id === 1) {

        $branches = Branch::where('id', '!=', 1)->get();

        foreach ($branches as $branch) {

            $already = Product::whereRaw('LOWER(TRIM(name)) = ?', [strtolower($name)])
                ->whereRaw('LOWER(TRIM(size)) = ?', [strtolower($size)])
                ->where('branch_id', $branch->id)
                ->exists();

            if (!$already) {
                Product::create([
                    'name'      => $name,
                    'size'      => $size,
                    'price'     => $price,
                    'stock'     => 0,
                    'color'     => 'N/A',
                    'branch_id' => $branch->id,
                ]);
            }
        }
    }

} else {
            $request->validate([
                'product_id' => 'required|exists:products,id',
                'branch_id'  => 'required|exists:branches,id',
                'price'      => 'nullable|numeric',
                'quantity'   => 'nullable|integer|min:0'
            ]);

            $qty = (int) ($request->quantity ?? 0);

            $existingProduct = Product::findOrFail($request->product_id);

            // Cashier auto uses existing price
            if (strtolower(auth()->user()->role) === 'cashier') {
                $request->merge([
                    'price' => $existingProduct->price
                ]);
            }

            $product = Product::where('name', $existingProduct->name)
                ->where('size', $existingProduct->size)
                ->where('branch_id', $request->branch_id)
                ->first();

            if ($product) {

                $product->stock += $qty;
                $product->price = $request->price;
                $product->save();

            } else {

                $product = Product::create([
                    'name'      => $existingProduct->name,
                    'size'      => $existingProduct->size,
                    'price'     => $request->price,
                    'stock'     => $qty,
                    'color'     => $existingProduct->color,
                    'branch_id' => $request->branch_id,
                ]);
            }

            if ($qty > 0) {
                StockMovement::create([
                    'product_id' => $product->id,
                    'branch_id'  => $request->branch_id,
                    'type'       => 'IN',
                    'quantity'   => $qty,
                    'reason'     => 'Manual Add',
                    'dr_number'  => $request->dr_number,
                    'unit_price' => $request->price,
                ]);
            }
        }

        DB::commit();

        return back()->with('success', 'Saved successfully!');

    } catch (\Exception $e) {

        DB::rollBack();

        return back()->with('error', $e->getMessage());
    }
}         

   public function overviewStock(Request $request)
{
    if (!Auth::check()) {
        return redirect('/login');
    }

    $user = Auth::user();
    $role = strtolower($user->role);

    $query = Product::with('branch');

    $selectedBranch = $request->branch_id;

    // Cashier default sariling branch
    if (!$selectedBranch && $role === 'cashier') {
        $selectedBranch = $user->branch_id;
    }

    if ($request->search) {
        $query->where('name', 'like', '%' . $request->search . '%');
    }

    if ($selectedBranch) {
        $query->where('branch_id', $selectedBranch);
    }

    $products = $query->latest()->paginate(10)->withQueryString();
    $branches = Branch::all();

    // UNIQUE TOTAL PRODUCTS (NAME + SIZE)
    $totalProducts = Product::when($selectedBranch, function ($q) use ($selectedBranch) {
            $q->where('branch_id', $selectedBranch);
        })
        ->get()
        ->unique(function ($item) {
            return strtolower(trim($item->name)) . '|' . strtolower(trim($item->size));
        })
        ->count();

    return view('admin.overview-stock', compact(
        'products',
        'branches',
        'totalProducts',
        'selectedBranch'
    ));
}
    // =====================
// EXPORT EXCEL
// =====================
public function exportExcel(Request $request)
{
    return Excel::download(
        new InventoryExport(
            $request->search,
            $request->branch_id
        ),
        'inventory-report.xlsx'
    );
}

    // =====================
    // EXPORT PDF
    // =====================
    public function exportPdf(Request $request)
    {
        $query = Product::with('branch');

        if ($request->search) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->branch_id) {
            $query->where('branch_id', $request->branch_id);
        }

        $products = $query->get();

        $pdf = Pdf::loadView('admin.inventory.pdf', compact('products'));

        return $pdf->stream('inventory-report.pdf');
    }

    // =====================
    // CASHIER: TRANSFER IN FORM
    // =====================
    public function transferInForm()
    {
        $products = Product::where('branch_id', '!=', auth()->user()->branch_id)->get();
        $branches = Branch::where('id', '!=', auth()->user()->branch_id)->get();

        $requests = StockMovement::with(['product','branch','from_branch'])
            ->where('type', 'IN_REQUEST')
            ->where('branch_id', auth()->user()->branch_id)
            ->whereIn('status', ['pending', 'approved_receiver'])
            ->latest()
            ->get();

        return view('cashier.transferin_cashier', compact('products', 'branches', 'requests'));
    }

   public function transferInStore(Request $request)
{
    // 🔥 FIX: mas reliable check
    if (!$request->has('items') || count($request->items) == 0) {
        return back()->with('error', 'No items selected');
    }

    // ✅ validation
    $request->validate([
        'from_branch_id' => 'required|exists:branches,id',
        'items' => 'required|array|min:1',
    ]);

    \DB::beginTransaction();

    try {

        foreach ($request->items as $item) {

            // ✅ safety check
            if (!isset($item['product_id'], $item['qty'])) {
                throw new \Exception('Invalid item data');
            }

            \App\Models\StockMovement::create([
                'product_id'     => $item['product_id'],
                'branch_id'      => auth()->user()->branch_id, // destination
                'from_branch_id' => $request->from_branch_id, // source
                'type'           => 'IN_REQUEST',
                'quantity'       => $item['qty'],
                'reason'         => 'Direct Transfer Request',
                'status'         => 'approved_sender',
                'requested_by'   => auth()->id(),
            ]);
        }

        \DB::commit();

        return back()->with('success', 'Transfer request sent to Incoming.');

    } catch (\Exception $e) {

        \DB::rollBack();

        return back()->with('error', $e->getMessage());
    }
}

   // =====================
// 🔥 ADMIN TRANSFER OUT
// =====================
public function transferOutAdmin(Request $request)
{
    $query = StockMovement::with([
        'product',
        'branch',
        'from_branch',
        'requester',
        'approver'
    ])
    ->where('type', 'IN_REQUEST');

    // 🔍 SEARCH
    if ($request->search) {
        $query->whereHas('product', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%');
        });
    }

    // 📌 STATUS FILTER
    if ($request->status) {
        $query->where('status', $request->status);
    }

    // 📊 EXPORT EXCEL
    if ($request->export == 'excel') {
        return Excel::download(
            new TransferOutExport($request->search, $request->status),
            'transfer-out-report.xlsx'
        );
    }

    // 📄 EXPORT PDF
    if ($request->export == 'pdf') {
        $transfers = $query->latest()->get();

        $pdf = Pdf::loadView(
            'admin.inventory.transfer-out-pdf',
            compact('transfers')
        );

        return $pdf->stream('transfer-out-report.pdf');
    }

    // 📃 NORMAL VIEW
    $transfers = $query->latest()->paginate(10)->withQueryString();

    return view('admin.inventory.transfer-out', compact('transfers'));
}

   // =====================
// 🔥 ADMIN TRANSFER IN
// =====================
public function transferInAdmin(Request $request)
{
    $query = StockMovement::with([
            'product',
            'branch',
            'requester',
            'approver'
        ])
        ->where('type', 'IN_REQUEST');

    // 🔍 SEARCH PRODUCT
    if ($request->search) {
        $query->whereHas('product', function ($q) use ($request) {
            $q->where('name', 'like', '%' . $request->search . '%');
        });
    }

    // 📌 STATUS FILTER
    if ($request->status) {
        $query->where('status', $request->status);
    }

    // 📄 EXPORT EXCEL
    if ($request->export == 'excel') {
        return Excel::download(
            new TransferInExport($request->search, $request->status),
            'transfer-in-report.xlsx'
        );
    }

    // 📄 EXPORT PDF
    if ($request->export == 'pdf') {
        $transfers = $query->latest()->get();

        $pdf = Pdf::loadView(
            'admin.inventory.transfer-in-pdf',
            compact('transfers')
        );

        return $pdf->stream('transfer-in-report.pdf');
    }

    // 📃 NORMAL VIEW
$transfers = $query->latest()->paginate(10)->withQueryString();

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
// 🔥 MANAGER DASHBOARD (UPDATED - WITH BRANCH FILTER)
// =====================
public function managerDashboard(Request $request)
{
    $branchId = $request->branch_id;

    // 🔥 GET ALL BRANCHES (para sa dropdown)
    $branches = \App\Models\Branch::all();

    // 🔥 REQUESTS (FILTERABLE)
    $requests = \App\Models\StockMovement::with(['product','branch','from_branch'])
        ->where('type', 'IN_REQUEST')
        ->where('status', 'pending')
        ->when($branchId, function ($query) use ($branchId) {
            $query->where('branch_id', $branchId);
        })
        ->latest()
        ->get();

    // 🔥 KPI (REAL DATA + FILTERED)

// TODAY SALES
$todaySales = \DB::table('sales')
    ->when($branchId, function ($query) use ($branchId) {
        $query->where('branch_id', $branchId);
    })
    ->whereDate('created_at', now()->toDateString())
    ->sum('total_amount');

// MONTHLY SALES
$monthlySales = \DB::table('sales')
    ->when($branchId, function ($query) use ($branchId) {
        $query->where('branch_id', $branchId);
    })
    ->whereMonth('created_at', now()->month)
    ->whereYear('created_at', now()->year)
    ->sum('total_amount');

// TOTAL ORDERS
$totalOrders = \DB::table('sales')
    ->when($branchId, function ($query) use ($branchId) {
        $query->where('branch_id', $branchId);
    })
    ->count();

// 🔥 LOW STOCK (FILTERED)
$lowStockCount = \App\Models\Product::when($branchId, function ($query) use ($branchId) {
        $query->where('branch_id', $branchId);
    })
    ->where('stock', '<=', 10)
    ->count();

return view('manager.dashboard', compact(
    'requests',
    'todaySales',
    'monthlySales',
    'totalOrders',
    'lowStockCount',
    'branches',
    'branchId'
));
}

    // =====================
// 🔥 APPROVAL PAGE (SIMPLIFIED FOR GLOBAL MANAGER)
// =====================
public function approvals()
{
    // 🔥 TOP: TRANSFER IN REQUESTS (PENDING)
    $transferInRequests = StockMovement::with(['product','branch','from_branch'])
        ->where('type', 'IN_REQUEST')
        ->where('status', 'pending')
        ->latest()
        ->get();

    // 🔥 BOTTOM: TRANSFER OUT REQUESTS (READY FOR RELEASE)
    $transferOutRequests = StockMovement::with(['product','branch','from_branch'])
        ->where('type', 'IN_REQUEST')
        ->where('status', 'approved_receiver')
        ->latest()
        ->get();

    return view('manager.approvals', compact(
        'transferInRequests',
        'transferOutRequests'
    ));
}

    // =====================
    // 🔥 APPROVE (DUAL FLOW)
    // =====================
    public function approve($id)
    {
        $movement = StockMovement::findOrFail($id);

        if ($movement->status == 'pending') {
            $movement->status = 'approved_receiver';
        } elseif ($movement->status == 'approved_receiver') {
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

    $role = strtolower($user->role);

    if (in_array($role, ['admin', 'manager', 'audit'])) {
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

    // =====================
    // 🔥 MANAGER TRANSFER OUT
    // =====================
    public function transferOutManager()
    {
        $branchId = auth()->user()->branch_id;

        $requests = StockMovement::with(['product','branch'])
            ->where('from_branch_id', $branchId)
            ->where('type', 'IN_REQUEST')
            ->where('status', 'approved_receiver')
            ->latest()
            ->get();

        return view('manager.transfer-out', compact('requests'));
    }

    // =====================
    // 🔥 RELEASE STOCK
    // =====================
    public function release($id)
    {
        $movement = StockMovement::findOrFail($id);

        $movement->status = 'approved_sender';
        $movement->released_by = auth()->id();
        $movement->released_at = now();
        $movement->save();

        return back()->with('success', 'Stock released!');
    }

    // =====================
    // 🔥 CASHIER INCOMING
    // =====================
    public function incoming()
    {
        $branchId = auth()->user()->branch_id;

        $requests = StockMovement::with(['product','from_branch'])
            ->where('branch_id', $branchId)
            ->where('type', 'IN_REQUEST')
            ->where('status', 'approved_sender')
            ->latest()
            ->get();

        return view('cashier.incoming', compact('requests'));
    }

    public function receive(Request $request, $id)
{
    $request->validate([
        'transfer_number' => 'required|string|max:100',
        'receive_remarks' => 'nullable|string|max:255',
    ]);

    DB::transaction(function () use ($request, $id) {

        $movement = StockMovement::findOrFail($id);

        // 🔥 Source Product (pinanggalingan ng stock)
        $sourceProduct = Product::findOrFail($movement->product_id);

        // ✅ Check kung sapat stock
        if ($sourceProduct->stock < $movement->quantity) {
            throw new \Exception('Insufficient stock to transfer.');
        }

        // 🔻 Bawas sa source branch
        $sourceProduct->stock -= $movement->quantity;
        $sourceProduct->save();

        // 🔥 Hanapin product sa destination branch
        $destinationProduct = Product::where('name', $sourceProduct->name)
            ->where('size', $sourceProduct->size)
            ->where('branch_id', $movement->branch_id)
            ->first();

        if ($destinationProduct) {

            // ➕ Dagdag stock kung existing na
            $destinationProduct->stock += $movement->quantity;
            $destinationProduct->save();

        } else {

            // 🆕 Create bagong product row sa destination branch
            Product::create([
                'name'      => $sourceProduct->name,
                'size'      => $sourceProduct->size,
                'price'     => $sourceProduct->price,
                'color'     => $sourceProduct->color,
                'stock'     => $movement->quantity,
                'branch_id' => $movement->branch_id,
            ]);
        }

        // ✅ Update transfer status + audit trail
        $movement->status = 'completed';
        $movement->transfer_number = $request->transfer_number;
        $movement->receive_remarks = $request->receive_remarks;
        $movement->received_by = auth()->id();
        $movement->received_at = now();
        $movement->save();
    });

    return back()->with('success', 'Stock received successfully!');
}
public function deliveryReport(Request $request)
{
    $branchId = $request->branch_id;

    // All branches for dropdown
    $branches = Branch::orderBy('name')->get();

    $query = StockMovement::with(['product', 'branch'])
        ->whereNotNull('dr_number')
        ->where('type', 'IN');

    // Filter specific branch
    if (!empty($branchId)) {
        $query->where('branch_id', $branchId);
    }

    // Summary totals (all filtered records)
    $summaryRows = (clone $query)->get();

    $totalDeliveries = $summaryRows->count();
    $totalQty = $summaryRows->sum('quantity');
    $totalValue = $summaryRows->sum(function ($row) {
        return $row->quantity * $row->unit_price;
    });

    $totalBranches = $branches->count();

    // Pagination
    $rows = $query->latest()
        ->paginate(10)
        ->withQueryString();

    return view('manager.delivery-report', compact(
        'rows',
        'branches',
        'branchId',
        'totalDeliveries',
        'totalQty',
        'totalValue',
        'totalBranches'
    ));
}
public function defectiveIndex()
{
    $branches = Branch::orderBy('name')->get();

    $products = Product::with('branch')
        ->orderBy('name')
        ->get();

    $rows = DefectiveStock::with(['product', 'branch', 'user'])
        ->latest()
        ->paginate(10);

    return view('manager.defective-stock', compact(
        'branches',
        'products',
        'rows'
    ));
}
public function defectiveStore(Request $request)
{
    $request->validate([
        'product_id' => 'required|exists:products,id',
        'quantity'   => 'required|integer|min:1',
        'reason'     => 'required|string|max:255',
    ]);

    DB::beginTransaction();

    try {
        $product = Product::findOrFail($request->product_id);

        if ($product->stock < $request->quantity) {
            return back()->with('error', 'Insufficient stock.');
        }

        // bawas stock
        $product->stock -= $request->quantity;
        $product->save();

        // save log
        DefectiveStock::create([
            'product_id' => $product->id,
            'branch_id'  => $product->branch_id,
            'quantity'   => $request->quantity,
            'reason'     => $request->reason,
            'user_id'    => auth()->id(),
        ]);

        DB::commit();

        return back()->with('success', 'Defective stock recorded.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}
public function syncProducts()
{
    DB::beginTransaction();

    try {

        $sourceProducts = Product::where('branch_id', 1)->get();
        $branches = Branch::where('id', '!=', 1)->get();

        $created = 0;

        foreach ($sourceProducts as $item) {
            foreach ($branches as $branch) {

                $exists = Product::where('name', $item->name)
                    ->where('size', $item->size)
                    ->where('branch_id', $branch->id)
                    ->exists();

                if (!$exists) {

                    $new = Product::create([
                        'name' => $item->name,
                        'size' => $item->size,
                        'price' => $item->price,
                        'stock' => 0,
                        'color' => $item->color,
                        'branch_id' => $branch->id,
                    ]);

                    $created++;
                }
            }
        }

        DB::commit();

        return back()->with('success', $created . ' synced successfully');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->with('error', $e->getMessage());
    }
}
}