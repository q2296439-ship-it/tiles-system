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
use App\Exports\RequestAccessExport;

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

public function loadReceipt($receipt_no)
{
    $branchId = auth()->user()->branch_id;

    $collection = Collection::with('items')
        ->where('receipt_no', $receipt_no)
        ->where('branch_id', $branchId)
        ->first();

    if (!$collection) {
        return response()->json([
            'success' => false,
            'message' => 'Receipt not found.'
        ]);
    }

    return response()->json([
        'success' => true,
        'customer_name' => $collection->customer_name,
        'items' => $collection->items->map(function ($item) {
            return [
                'qty' => $item->qty,
                'unit' => $item->unit,
                'description' => $item->description,
                'unit_price' => $item->unit_price,
                'amount' => $item->amount,
                'discount_amount' => $item->discount_amount ?? 0,
                'net_amount' => $item->net_amount ?? $item->amount,
                'return_amount' => $item->net_amount ?? $item->amount,
            ];
        })->values()
    ]);
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

                    $product = Product::whereRaw('LOWER(TRIM(name)) = ?', [strtolower(trim($desc))])
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

        $total = -abs($records->sum('total_amount'));
        $actualCollection = 0;

    } elseif ($status == 'cancelled') {

        $total = 0;
        $actualCollection = 0;

    } elseif ($status == 'saved') {

        $total = $records->where('record_type', 'saved')->sum('total_amount')
               - $records->where('record_type', 'returned')->sum('total_amount');

        $actualCollection = $records->where('record_type', 'saved')->sum('paid_amount');

    } else {

        $salesTotal = $records
            ->where('record_type', 'saved')
            ->sum('total_amount');

        $returnTotal = $records
            ->where('record_type', 'returned')
            ->sum('total_amount');

        $total = $salesTotal - $returnTotal;

        $actualCollection = $records
            ->where('record_type', 'saved')
            ->sum('paid_amount');
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
        'actualCollection',
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
        ->where('status', '!=', 'cancelled');

    if ($role === 'cashier') {
        $query->where('branch_id', $user->branch_id);
        $selectedBranch = $user->branch_id;
    } elseif (!empty($selectedBranch)) {
        $query->where('branch_id', $selectedBranch);
    }

    $rows = $query->get();

    $returnRows = ReturnModel::whereDate('return_date', $selectedDate);

    if ($role === 'cashier') {
        $returnRows->where('branch_id', $user->branch_id);
    } elseif (!empty($selectedBranch)) {
        $returnRows->where('branch_id', $selectedBranch);
    }

    $returns = $returnRows->get();

    $gross = $rows->sum('total_amount') - $returns->sum('total_amount');
    $discount = $rows->sum('discount_amount');
    $net = $gross - $discount;

    $depositQuery = \App\Models\Deposit::where('deposit_date', $selectedDate);

    if ($role === 'cashier') {
        $depositQuery->where('branch_id', $user->branch_id);
    } elseif (!empty($selectedBranch)) {
        $depositQuery->where('branch_id', $selectedBranch);
    }

    $deposit = $depositQuery
        ->where('status', 'closed')
        ->latest('id')
        ->first();

    if ($deposit) {

        $gross = $deposit->gross_amount;
        $discount = $deposit->discount_amount;
        $net = $deposit->net_amount;

        $actual = $deposit->actual_amount;
        $variance = $deposit->variance;

        $ar_balance = $deposit->ar_balance ?? 0;
        $store_expenses = $deposit->store_expenses ?? 0;
        $delivery_fee = $deposit->delivery_fee ?? 0;
        $other_income = $deposit->other_income ?? 0;

        $isClosed = true;

    } else {

        $actual = 0;
        $variance = 0 - $net;

        $ar_balance = 0;
        $store_expenses = 0;
        $delivery_fee = 0;
        $other_income = 0;

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
        'selectedBranch',
        'ar_balance',
        'store_expenses',
        'delivery_fee',
        'other_income'
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
    $branchName = optional($user->branch)->name ?? 'Current Branch';

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
        'total',
        'branchName'
    ))->setPaper('a4', 'landscape');

    $agent = strtolower($request->header('User-Agent'));

    $isMobile =
        str_contains($agent, 'android') ||
        str_contains($agent, 'iphone') ||
        str_contains($agent, 'ipad') ||
        str_contains($agent, 'mobile');

    if ($isMobile) {
        return response()->make($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="collection_report_' . $selectedDate . '.pdf"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'public',
            'Expires' => '0',
        ]);
    }

    return $pdf->stream('collection_report_' . $selectedDate . '.pdf');
}

public function exportExcel(Request $request)
{
    $selectedDate = $request->date ?? date('Y-m-d');
    $status = $request->status ?? 'all';

    $user = auth()->user();

    return Excel::download(
        new CollectionExport(
            $selectedDate,
            $user->branch_id,
            $status
        ),
        'collection_report_' . $selectedDate . '.xlsx',
        \Maatwebsite\Excel\Excel::XLSX,
        [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Cache-Control' => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma' => 'public',
            'Expires' => '0',
        ]
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

        // bawal magsave kapag closed na deposit date
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

            $gross    = (float) ($request->gross_amount ?? $request->total_amount ?? 0);
            $discount = (float) ($request->discount_amount ?? 0);
            $net      = (float) ($request->total_amount ?? 0);

            $salesType = strtolower($request->sales_type ?? 'cash');

            $paid = (float) ($request->paid_amount ?? 0);
            $balance = 0;
            $status = 'saved';

            if (in_array($salesType, ['dp', 'partial']) && $paid <= 0) {
                throw new \Exception('Paid amount is required for DP or Partial payment.');
            }

            if ($salesType === 'cash') {

                $paid = $net;
                $balance = 0;
                $status = 'saved';

            } else {

                if ($paid > $net) {
                    $paid = $net;
                }

                $balance = $net - $paid;

                if ($balance > 0) {
                    $status = 'pending';
                } else {
                    $balance = 0;
                    $status = 'saved';
                }
            }

            $collection = Collection::create([
                'receipt_no'      => $request->receipt_no,
                'receipt_date'    => $request->receipt_date,
                'customer_name'   => $request->customer_name ?? '',
                'business_style'  => $request->business_style ?? '',
                'address'         => $request->address ?? '',
                'terms'           => $request->terms ?? '',

                'gross_amount'    => $gross,
                'discount_type'   => $request->discount_type ?? null,
                'discount_amount' => $discount,
                'net_amount'      => $net,
                'total_amount'    => $net,

                'sales_type'      => $salesType,
                'paid_amount'     => $paid,
                'balance'         => $balance,

                'branch_id'       => $branchId,
                'user_id'         => $userId,
                'status'          => $status,
                'cancel_reason'   => null,
            ]);

            $sale = Sale::create([
                'total_amount' => $net,
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
            ->with('success', 'Receipt saved successfully!');

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
        ->where('status', '!=', 'cancelled')
        ->get();

    $returns = ReturnModel::whereDate('return_date', $date)
        ->where('branch_id', $branchId)
        ->get();

    $gross = $rows->sum('total_amount') - $returns->sum('total_amount');
    $discount = $rows->sum('discount_amount');
    $net = $gross - $discount;

    $actual = (float) $request->actual_amount;

    $ar = (float) ($request->ar_balance ?? 0);
    $expense = (float) ($request->store_expenses ?? 0);
    $delivery = (float) ($request->delivery_fee ?? 0);
    $other = (float) ($request->other_income ?? 0);

    $otherIncome = $delivery + $other;

    $expected = $gross + $otherIncome - $discount;
    $variance = ($actual + $ar + $expense) - $expected;

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
        'user_id' => auth()->id(),

        'expected_amount' => $expected,
        'gross_amount' => $gross,
        'discount_amount' => $discount,
        'net_amount' => $expected,
        'actual_amount' => $actual,
        'variance' => $variance,

        'ar_balance' => $ar,
        'store_expenses' => $expense,
        'delivery_fee' => $delivery,
        'other_income' => $other,

        'denom_1000' => $request->denom_1000 ?? 0,
        'denom_500'  => $request->denom_500 ?? 0,
        'denom_200'  => $request->denom_200 ?? 0,
        'denom_100'  => $request->denom_100 ?? 0,
        'denom_50'   => $request->denom_50 ?? 0,
        'denom_20'   => $request->denom_20 ?? 0,
        'coin_10'    => $request->coin_10 ?? 0,
        'coin_5'     => $request->coin_5 ?? 0,
        'coin_1'     => $request->coin_1 ?? 0,

        'remarks' => $request->remarks,
        'status' => 'closed',
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

public function deliveryFeeForm()
{
    $today = date('Ymd');
    $branchId = auth()->user()->branch_id;

    $count = DB::table('delivery_fees')
        ->whereDate('delivery_date', date('Y-m-d'))
        ->where('branch_id', $branchId)
        ->count() + 1;

    $deliveryNo = 'DF-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

    return view('cashier.delivery_fee', compact('deliveryNo'));
}

public function deliveryStore(Request $request)
{
    $request->validate([
        'delivery_date' => 'required|date',
        'receipt_no'    => 'required',
        'amount'        => 'required|numeric|min:0',
        'status'        => 'required',
    ]);

    $branchId = auth()->user()->branch_id;
    $today = date('Ymd');

    $count = DB::table('delivery_fees')
        ->whereDate('delivery_date', $request->delivery_date)
        ->where('branch_id', $branchId)
        ->count() + 1;

    $deliveryNo = 'DF-' . $today . '-' . str_pad($count, 4, '0', STR_PAD_LEFT);

    DB::table('delivery_fees')->insert([
        'delivery_no'   => $deliveryNo,
        'delivery_date' => $request->delivery_date,
        'receipt_no'    => $request->receipt_no,
        'customer_name' => $request->customer_name,
        'address'       => $request->address,
        'rider_name'    => $request->rider_name,
        'amount'        => $request->amount,
        'notes'         => $request->notes,
        'branch_id'     => $branchId,
        'user_id'       => auth()->id(),
        'status'        => $request->status,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);

    return redirect()
        ->route('cashier.delivery.fee')
        ->with('success', 'Delivery fee saved successfully.');
}

public function deliveryToday(Request $request)
{
    $selectedDate = $request->date ?? date('Y-m-d');
    $branchId = auth()->user()->branch_id;

    $rows = DB::table('delivery_fees')
        ->whereDate('delivery_date', $selectedDate)
        ->where('branch_id', $branchId)
        ->orderBy('id', 'desc')
        ->paginate(10)
        ->appends([
            'date' => $selectedDate
        ]);

    $totalCount = DB::table('delivery_fees')
        ->whereDate('delivery_date', $selectedDate)
        ->where('branch_id', $branchId)
        ->count();

    $income = DB::table('delivery_fees')
        ->whereDate('delivery_date', $selectedDate)
        ->where('branch_id', $branchId)
        ->where('status', 'saved')
        ->sum('amount');

    $refund = DB::table('delivery_fees')
        ->whereDate('delivery_date', $selectedDate)
        ->where('branch_id', $branchId)
        ->where('status', 'returned')
        ->sum('amount');

    $totalIncome = $income - $refund;

    return view('cashier.delivery_today', compact(
        'rows',
        'selectedDate',
        'totalCount',
        'totalIncome'
    ));
}

public function deliveryExcel(Request $request)
{
    $selectedDate = $request->date ?? date('Y-m-d');
    $branchId = auth()->user()->branch_id;

    $rows = DB::table('delivery_fees')
        ->whereDate('delivery_date', $selectedDate)
        ->where('branch_id', $branchId)
        ->orderBy('id', 'desc')
        ->get();

    $filename = 'delivery-report-' . $selectedDate . '.csv';

    $headers = [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="'.$filename.'"',
    ];

    $callback = function () use ($rows) {
        $file = fopen('php://output', 'w');

        fputcsv($file, [
            '#',
            'Delivery No',
            'Receipt No',
            'Customer',
            'Amount',
            'Status',
            'Date'
        ]);

        foreach ($rows as $index => $row) {
            fputcsv($file, [
                $index + 1,
                $row->delivery_no,
                $row->receipt_no,
                $row->customer_name,
                $row->amount,
                $row->status,
                $row->delivery_date
            ]);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function arAccounts(Request $request)
{
    $branchId = auth()->user()->branch_id;

    $query = Collection::where('branch_id', $branchId)
        ->whereIn('sales_type', ['dp', 'partial'])
        ->where('balance', '>', 0);

    if ($request->search) {
        $query->where(function ($q) use ($request) {
            $q->where('receipt_no', 'like', '%' . $request->search . '%')
              ->orWhere('customer_name', 'like', '%' . $request->search . '%');
        });
    }

    if ($request->date) {
        $query->whereDate('receipt_date', $request->date);
    }

    $rows = $query->latest()->paginate(10);

    return view('cashier.ar_accounts', compact('rows'));
}

public function payAr(Request $request, $id)
{
    $request->validate([
        'payment' => 'required|numeric|min:1'
    ]);

    $row = Collection::findOrFail($id);

    $row->paid_amount += $request->payment;
    $row->balance = $row->total_amount - $row->paid_amount;

    if ($row->balance <= 0) {
        $row->balance = 0;
        $row->status = 'saved';
    } else {
        $row->status = 'pending';
    }

    $row->save();

    return back()->with('success', 'Payment saved successfully.');
}


}