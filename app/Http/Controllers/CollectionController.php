<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\ReturnModel;
use App\Models\ReturnItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\CollectionExport;
use App\Exports\DepositExport;


class CollectionController extends Controller
{
    public function create()
{
    $today = date('Y-m-d');
    $branchId = auth()->user()->branch_id;

    $isClosed = \App\Models\Deposit::whereDate('deposit_date', $today)
        ->where('branch_id', $branchId)
        ->where('status', 'closed')
        ->exists();

    return view('cashier.collection.create', compact('isClosed'));
}
    public function cancelForm()
    {
        return view('cashier.collection.cancel');
    }

    public function returnForm()
    {
        return view('cashier.collection.return');
    }

    // CANCEL = documentation only
    public function cancelStore(Request $request)
    {
        $request->validate([
            'receipt_no'    => 'required',
            'receipt_date'  => 'required|date',
            'customer_name' => 'nullable|string',
            'cancel_reason' => 'required|string',
        ]);

        Collection::create([
            'receipt_no'    => $request->receipt_no,
            'receipt_date'  => $request->receipt_date,
            'customer_name' => $request->customer_name ?? '',
            'address'       => '',
            'terms'         => '',
            'total_amount'  => 0,
            'branch_id'     => auth()->user()->branch_id,
            'user_id'       => auth()->id(),
            'status'        => 'cancelled',
            'cancel_reason' => $request->cancel_reason,
        ]);

        return redirect()
            ->route('cashier.collection.cancel')
            ->with('success', 'Cancelled receipt saved!');
    }

    // RETURN = stock back + sales less
    public function returnStore(Request $request)
    {
        try {

            $request->validate([
                'return_no'   => 'required|unique:returns,return_no',
                'return_date' => 'required|date',
                'reason'      => 'required',
                'items'       => 'required|array|min:1',
            ]);

            DB::transaction(function () use ($request) {

                $branchId = auth()->user()->branch_id;
                $userId   = auth()->id();

                $return = ReturnModel::create([
                    'return_no'     => $request->return_no,
                    'receipt_no'    => $request->receipt_no,
                    'return_date'   => $request->return_date,
                    'customer_name' => $request->customer_name ?? '',
                    'reason'        => $request->reason,
                    'total_amount'  => $request->total_amount ?? 0,
                    'branch_id'     => $branchId,
                    'user_id'       => $userId,
                ]);

                $sale = Sale::create([
                    'total_amount' => -1 * ($request->total_amount ?? 0),
                    'branch_id'    => $branchId,
                    'user_id'      => $userId,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                foreach ($request->items as $item) {

                    if (empty($item['description'])) {
                        continue;
                    }

                    $qty    = (int) ($item['qty'] ?? 0);
                    $price  = (float) ($item['unit_price'] ?? 0);
                    $amount = $qty * $price;
                    $desc   = trim($item['description']);

                    $product = Product::where('name', $desc)
                        ->where('branch_id', $branchId)
                        ->first();

                    ReturnItem::create([
                        'return_id'   => $return->id,
                        'product_id'  => $product->id ?? null,
                        'qty'         => $qty,
                        'unit'        => $item['unit'] ?? '',
                        'description' => $desc,
                        'unit_price'  => $price,
                        'amount'      => $amount,
                    ]);

                    if ($product) {
                        $product->stock += $qty;
                        $product->save();

                        SaleItem::create([
                            'sale_id'    => $sale->id,
                            'product_id' => $product->id,
                            'quantity'   => -1 * $qty,
                            'price'      => $price,
                            'subtotal'   => -1 * $amount,
                        ]);
                    }
                }
            });

            return redirect()
                ->route('cashier.return.create')
                ->with('success', 'Return receipt saved, stock restored, sales adjusted!');

        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function today(Request $request)
    {
        $selectedDate = $request->date ?? date('Y-m-d');
        $status       = $request->status ?? 'all';

        $user = auth()->user();
        $isAdmin = strtolower($user->role) === 'admin';
        $branchId = $user->branch_id;

        $collections = Collection::with(['user', 'items', 'branch'])
            ->whereDate('receipt_date', $selectedDate);

        if (!$isAdmin) {
            $collections->where('branch_id', $branchId);
        }

        $collections = $collections
            ->get()
            ->map(function ($row) {
                $row->record_type = strtolower($row->status ?? 'saved');
                return $row;
            });

        $returns = ReturnModel::with(['user', 'items', 'branch'])
            ->whereDate('return_date', $selectedDate);

        if (!$isAdmin) {
            $returns->where('branch_id', $branchId);
        }

        $returns = $returns
            ->get()
            ->map(function ($row) {
                $row->display_receipt_no = $row->receipt_no ?: $row->return_no;
                $row->receipt_date = $row->return_date;
                $row->status = 'returned';
                $row->record_type = 'returned';
                return $row;
            });

        $records = $collections->concat($returns);

        if ($status != 'all') {
            $records = $records->where('record_type', $status);
        }

        $records = $records->sortByDesc('created_at')->values();

        if ($status == 'returned') {
            $total = -1 * $records->sum('total_amount');
        } elseif ($status == 'cancelled') {
            $total = 0;
        } elseif ($status == 'saved') {
            $total = $records->sum('total_amount');
        } else {
            $savedTotal  = $records->where('record_type', 'saved')->sum('total_amount');
            $returnTotal = $records->where('record_type', 'returned')->sum('total_amount');
            $total = $savedTotal - $returnTotal;
        }

        $page = LengthAwarePaginator::resolveCurrentPage();
        $perPage = 10;

        $collections = new LengthAwarePaginator(
            $records->forPage($page, $perPage)->values(),
            $records->count(),
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return view('cashier.collection.today', compact(
            'collections',
            'total',
            'selectedDate',
            'status'
        ));
    }

    public function deposit(Request $request)
{
    $selectedDate = $request->date ?? date('Y-m-d');

    $user = auth()->user();
    $role = strtolower($user->role);

    $selectedBranch = $request->branch_id;

    $query = Collection::whereDate('receipt_date', $selectedDate)
        ->where('status', 'saved');

    if ($role === 'cashier') {
        $query->where('branch_id', $user->branch_id);
        $selectedBranch = $user->branch_id;
    } elseif (!empty($selectedBranch)) {
        $query->where('branch_id', $selectedBranch);
    }

    $rows = $query->get();

    $gross = $rows->sum('gross_amount');
    $discount = $rows->sum('discount_amount');
    $net = $rows->sum('net_amount');

    $depositQuery = \App\Models\Deposit::where('deposit_date', $selectedDate);

    if ($role === 'cashier') {
        $depositQuery->where('branch_id', $user->branch_id);
    } elseif (!empty($selectedBranch)) {
        $depositQuery->where('branch_id', $selectedBranch);
    }

    $deposit = $depositQuery->where('status', 'closed')->first();

    if ($deposit) {
        $actual = $deposit->actual_amount;
        $variance = $deposit->variance;
        $isClosed = true;
    } else {
        $actual = 0;
        $variance = 0 - $net;
        $isClosed = false;
    }

    $branches = \App\Models\Branch::all();

    return view('cashier.deposit.index', compact(
        'gross',
        'discount',
        'net',
        'actual',
        'variance',
        'selectedDate',
        'isClosed',
        'branches',
        'selectedBranch'
    ));
}

    public function exportPdf(Request $request)
{
    $selectedDate = $request->date ?? date('Y-m-d');
    $status = $request->status ?? 'all';

    $user = auth()->user();
    $role = strtolower($user->role);

    $isAdmin = $role === 'admin';
    $branchId = $user->branch_id ?: 1;

    $collections = Collection::with(['user', 'items', 'branch'])
        ->whereDate('receipt_date', $selectedDate);

    if (!$isAdmin) {
        $collections->where('branch_id', $branchId);
    }

    $collections = $collections->get()->map(function ($row) {
        $row->record_type = strtolower($row->status ?? 'saved');
        return $row;
    });

    $returns = ReturnModel::with(['user', 'items', 'branch'])
        ->whereDate('return_date', $selectedDate);

    if (!$isAdmin) {
        $returns->where('branch_id', $branchId);
    }

    $returns = $returns->get()->map(function ($row) {
        $row->display_receipt_no = $row->receipt_no ?: $row->return_no;
        $row->receipt_date = $row->return_date;
        $row->status = 'returned';
        $row->record_type = 'returned';
        return $row;
    });

    $records = $collections->concat($returns);

    if ($status != 'all') {
        $records = $records->where('record_type', $status);
    }

    $collections = $records->sortByDesc('created_at')->values();

    if ($status == 'returned') {
        $total = -1 * $collections->sum('total_amount');
    } elseif ($status == 'cancelled') {
        $total = 0;
    } elseif ($status == 'saved') {
        $total = $collections->sum('total_amount');
    } else {
        $savedTotal  = $collections->where('record_type', 'saved')->sum('total_amount');
        $returnTotal = $collections->where('record_type', 'returned')->sum('total_amount');
        $total = $savedTotal - $returnTotal;
    }

    $pdf = Pdf::loadView('cashier.collection.pdf', compact(
        'collections',
        'selectedDate',
        'status',
        'total'
    ))->setPaper('a4', 'landscape');

    return $pdf->stream('collection_report_' . $selectedDate . '.pdf');
}

    public function exportExcel(Request $request)
    {
        $selectedDate = $request->date ?? date('Y-m-d');
        $status = $request->status ?? 'all';

        return Excel::download(
            new CollectionExport(
                $selectedDate,
                auth()->user()->branch_id,
                $status
            ),
            'collection_report_' . $selectedDate . '.xlsx'
        );
    }

    public function store(Request $request)
    {
        try {

            $request->validate([
                'receipt_no'   => 'required|unique:collections,receipt_no',
                'receipt_date' => 'required|date',
                'items'        => 'required|array|min:1',
            ]);

           // ✅ NEW LOCK: bawal magsave kapag closed na deposit date
$isClosed = \App\Models\Deposit::whereDate('deposit_date', $request->receipt_date)
    ->where('branch_id', auth()->user()->branch_id)
    ->where('status', 'closed')
    ->exists();

            if ($isClosed) {
                return back()
                    ->withInput()
                    ->with('error', 'This date is already deposited and closed. Saving is disabled.');
            }

            DB::transaction(function () use ($request) {

                $branchId = auth()->user()->branch_id;
                $userId   = auth()->id();

                $gross = $request->gross_amount ?? $request->total_amount ?? 0;
                $discount = $request->discount_amount ?? 0;
                $net = $request->total_amount ?? 0;

                $collection = Collection::create([
                    'receipt_no'      => $request->receipt_no,
                    'receipt_date'    => $request->receipt_date,
                    'customer_name'   => $request->customer_name ?? '',
                    'address'         => $request->address ?? '',
                    'terms'           => $request->terms ?? '',
                    'gross_amount'    => $gross,
                    'discount_type'   => $request->discount_type ?? null,
                    'discount_amount' => $discount,
                    'net_amount'      => $net,
                    'total_amount'    => $net,
                    'branch_id'       => $branchId,
                    'user_id'         => $userId,
                    'status'          => 'saved',
                    'cancel_reason'   => null,
                ]);

                $sale = Sale::create([
                    'total_amount' => $request->total_amount ?? 0,
                    'branch_id'    => $branchId,
                    'user_id'      => $userId,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);

                foreach ($request->items as $item) {

                    if (empty($item['description'])) {
                        continue;
                    }

                    $qty    = (int) ($item['qty'] ?? 0);
                    $price  = (float) ($item['unit_price'] ?? 0);
                    $amount = $qty * $price;
                    $desc   = trim($item['description']);

                    CollectionItem::create([
                        'collection_id' => $collection->id,
                        'qty'           => $qty,
                        'unit'          => $item['unit'] ?? '',
                        'description'   => $desc,
                        'unit_price'    => $price,
                        'amount'        => $amount,
                    ]);

                    $product = Product::where('name', $desc)
                        ->where('branch_id', $branchId)
                        ->first();

                    if (!$product) {
                        throw new \Exception('Product not found: ' . $desc);
                    }

                    if ($product->stock < $qty) {
                        throw new \Exception('Not enough stock: ' . $desc);
                    }

                    $product->stock -= $qty;
                    $product->save();

                    SaleItem::create([
                        'sale_id'    => $sale->id,
                        'product_id' => $product->id,
                        'quantity'   => $qty,
                        'price'      => $price,
                        'subtotal'   => $amount,
                    ]);
                }
            });

            return redirect()
                ->route('cashier.collection.create')
                ->with('success', 'Receipt saved and reflected to sales!');

        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }


    // ==========================
// 🔓 MANAGER REQUEST ACCESS
// ==========================
public function requestAccess(Request $request)
{
    $query = \App\Models\Deposit::with(['user'])
        ->leftJoin('branches', 'deposits.branch_id', '=', 'branches.id')
        ->select('deposits.*', 'branches.name as branch_name')
        ->where('deposits.status', 'closed');

    if ($request->date) {
        $query->whereDate('deposits.deposit_date', $request->date);
    }

    if ($request->branch_id) {
        $query->where('deposits.branch_id', $request->branch_id);
    }

    $closedDates = $query->latest('deposits.id')->paginate(10);

    return view('manager.request-access', compact('closedDates'));
}

public function openTransaction(Request $request)
{
    $request->validate([
        'id' => 'required|exists:deposits,id',
        'reopen_reason' => 'nullable|string|max:255'
    ]);

    $deposit = \App\Models\Deposit::findOrFail($request->id);

    $deposit->status = 'reopened';
    $deposit->reopened_by = auth()->id();
    $deposit->reopened_at = now();
    $deposit->reopen_reason = $request->reopen_reason;
    $deposit->save();

    return redirect()
        ->route('manager.request.access')
        ->with('success', 'Transaction reopened successfully.');
}

public function depositStore(Request $request)
{
    $request->validate([
        'deposit_date'  => 'required|date',
        'actual_amount' => 'required|numeric|min:0',
    ]);

    $branchId = auth()->user()->branch_id;
    $date = $request->deposit_date;

    $rows = Collection::whereDate('receipt_date', $date)
        ->where('branch_id', $branchId)
        ->where('status', 'saved')
        ->get();

    $net = $rows->sum('net_amount');
    $actual = $request->actual_amount;
    $variance = $actual - $net;

    $existing = \App\Models\Deposit::where('deposit_date', $date)
    ->where('branch_id', $branchId)
    ->where('status', 'closed')
    ->first();

if ($existing) {
    return redirect()
        ->route('cashier.deposit')
        ->with('error', 'Deposit already closed for this date.');
}

\App\Models\Deposit::create([
    'deposit_date' => $date,
    'branch_id' => $branchId,

    'gross_amount' => $rows->sum('gross_amount'),
    'discount_amount' => $rows->sum('discount_amount'),
    'net_amount' => $net,
    'actual_amount' => $actual,
    'variance' => $variance,
    'user_id' => auth()->id(),

    'denom_1000' => $request->denom_1000 ?? 0,
    'denom_500'  => $request->denom_500 ?? 0,
    'denom_200'  => $request->denom_200 ?? 0,
    'denom_100'  => $request->denom_100 ?? 0,
    'denom_50'   => $request->denom_50 ?? 0,
    'denom_20'   => $request->denom_20 ?? 0,
    'coin_10'    => $request->coin_10 ?? 0,
    'coin_5'     => $request->coin_5 ?? 0,
    'coin_1'     => $request->coin_1 ?? 0,
    'remarks'    => $request->remarks,
]);

return redirect()
    ->route('cashier.deposit')
    ->with('success', 'Deposit saved successfully.');
}

// ==========================
// 📗 DEPOSIT EXCEL EXPORT
// ==========================
public function depositExcel(Request $request)
{
    $date = $request->date ?? date('Y-m-d');
    $branchId = $request->branch_id;
    $user = auth()->user();

    return Excel::download(
        new DepositExport(
            $date,
            $branchId,
            strtolower($user->role),
            $user->branch_id
        ),
        'deposit-report.xlsx'
    );
}

// ==========================
// 📄 DEPOSIT PDF EXPORT
// ==========================
public function depositPdf(Request $request)
{
    $date = $request->date ?? date('Y-m-d');
    $branchId = $request->branch_id;
    $user = auth()->user();

    $query = \App\Models\Deposit::leftJoin('branches', 'deposits.branch_id', '=', 'branches.id')
        ->select('deposits.*', 'branches.name as branch_name')
        ->whereDate('deposits.deposit_date', $date);

    if (strtolower($user->role) === 'cashier') {
        $query->where('deposits.branch_id', $user->branch_id);
    } elseif (!empty($branchId)) {
        $query->where('deposits.branch_id', $branchId);
    }

    $rows = collect([
    $query->where('deposits.status', 'closed')
        ->latest('deposits.id')
        ->first()
])->filter();

    $pdf = Pdf::loadView('cashier.deposit.pdf', compact('rows', 'date'))
        ->setPaper('a4', 'portrait');

    return $pdf->stream('deposit-report.pdf');
}
public function managerCollection(Request $request)
{
    $selectedDate = $request->date ?? date('Y-m-d');
    $status = $request->status ?? 'all';

    $manager = auth()->user();
    $branchId = $manager->branch_id ?: 1;

    $collections = Collection::with(['user', 'items', 'branch'])
        ->whereDate('receipt_date', $selectedDate)
        ->where('branch_id', $branchId)
        ->get()
        ->map(function ($row) {
            $row->record_type = strtolower($row->status ?? 'saved');
            return $row;
        });

    $returns = ReturnModel::with(['user', 'items', 'branch'])
        ->whereDate('return_date', $selectedDate)
        ->where('branch_id', $branchId)
        ->get()
        ->map(function ($row) {
            $row->display_receipt_no = $row->receipt_no ?: $row->return_no;
            $row->receipt_date = $row->return_date;
            $row->status = 'returned';
            $row->record_type = 'returned';
            return $row;
        });

    $records = $collections->concat($returns);

    if ($status != 'all') {
        $records = $records->where('record_type', $status);
    }

    $records = $records->sortByDesc('created_at')->values();

    if ($status == 'returned') {
        $total = -1 * $records->sum('total_amount');
    } elseif ($status == 'cancelled') {
        $total = 0;
    } elseif ($status == 'saved') {
        $total = $records->sum('total_amount');
    } else {
        $savedTotal = $records->where('record_type', 'saved')->sum('total_amount');
        $returnTotal = $records->where('record_type', 'returned')->sum('total_amount');
        $total = $savedTotal - $returnTotal;
    }

    $page = LengthAwarePaginator::resolveCurrentPage();
    $perPage = 10;

    $collections = new LengthAwarePaginator(
        $records->forPage($page, $perPage)->values(),
        $records->count(),
        $perPage,
        $page,
        [
            'path' => request()->url(),
            'query' => request()->query(),
        ]
    );

    return view('cashier.collection.today', compact(
        'collections',
        'total',
        'selectedDate',
        'status'
    ));
}
// ==========================
// 📄 REQUEST ACCESS PDF
// ==========================
public function requestAccessPdf(Request $request)
{
    $query = \App\Models\Deposit::with(['user'])
        ->leftJoin('branches', 'deposits.branch_id', '=', 'branches.id')
        ->select('deposits.*', 'branches.name as branch_name')
        ->where('deposits.status', 'closed');

    if ($request->date) {
        $query->whereDate('deposits.deposit_date', $request->date);
    }

    if ($request->branch_id) {
        $query->where('deposits.branch_id', $request->branch_id);
    }

    $rows = $query->latest('deposits.id')->get();

    $pdf = Pdf::loadView('manager.request-access-pdf', compact('rows'))
        ->setPaper('a4', 'landscape');

    return $pdf->stream('request-access-report.pdf');
}

// ==========================
// 📗 REQUEST ACCESS EXCEL
// ==========================
public function requestAccessExcel(Request $request)
{
    return Excel::download(
        new RequestAccessExport(
            $request->date,
            $request->branch_id
        ),
        'request-access-report.xlsx'
    );
}

}