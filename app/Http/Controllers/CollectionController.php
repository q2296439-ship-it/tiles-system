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

class CollectionController extends Controller
{
    public function create()
    {
        return view('cashier.collection.create');
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
        $branchId     = auth()->user()->branch_id;

        $collections = Collection::with(['user', 'items'])
            ->whereDate('receipt_date', $selectedDate)
            ->where('branch_id', $branchId)
            ->get()
            ->map(function ($row) {
                $row->record_type = strtolower($row->status ?? 'saved');
                return $row;
            });

        $returns = ReturnModel::with(['user', 'items'])
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

    // ✅ FIXED: ISA LANG ANG DEPOSIT METHOD
    public function deposit(Request $request)
    {
        $selectedDate = $request->date ?? date('Y-m-d');
        $branchId = auth()->user()->branch_id;

        $rows = Collection::whereDate('receipt_date', $selectedDate)
            ->where('branch_id', $branchId)
            ->where('status', 'saved')
            ->get();

        $gross = $rows->sum('gross_amount');
        $discount = $rows->sum('discount_amount');
        $net = $rows->sum('net_amount');

        $deposit = \App\Models\Deposit::where('deposit_date', $selectedDate)
            ->where('branch_id', $branchId)
            ->first();

        if ($deposit) {
            $actual = $deposit->actual_amount;
            $variance = $deposit->variance;
            $isClosed = true;
        } else {
            $actual = 0;
            $variance = 0 - $net;
            $isClosed = false;
        }

        return view('cashier.deposit.index', compact(
            'gross',
            'discount',
            'net',
            'actual',
            'variance',
            'selectedDate',
            'isClosed'
        ));
    }

    public function exportPdf(Request $request)
    {
        $selectedDate = $request->date ?? date('Y-m-d');
        $status = $request->status ?? 'all';
        $branchId = auth()->user()->branch_id;

        $collections = Collection::with(['user', 'items'])
            ->whereDate('receipt_date', $selectedDate)
            ->where('branch_id', $branchId)
            ->get()
            ->map(function ($row) {
                $row->record_type = strtolower($row->status ?? 'saved');
                return $row;
            });

        $returns = ReturnModel::with(['user', 'items'])
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

        $collections = $records->sortByDesc('created_at')->values();

        $pdf = Pdf::loadView('cashier.collection.pdf', compact(
            'collections',
            'selectedDate',
            'status'
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
}