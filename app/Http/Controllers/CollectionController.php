<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\CollectionExport;

class CollectionController extends Controller
{
    // Show Add Collection Receipt Page
    public function create()
    {
        return view('cashier.collection.create');
    }

    // Collection Report Page (Today + Previous Dates)
    public function today(Request $request)
    {
        $selectedDate = $request->date ?? date('Y-m-d');

        $collections = Collection::with(['user', 'items'])
            ->whereDate('created_at', $selectedDate)
            ->where('branch_id', auth()->user()->branch_id)
            ->latest()
            ->get();

        $total = $collections->sum('total_amount');

        return view('cashier.collection.today', compact(
            'collections',
            'total',
            'selectedDate'
        ));
    }

    // EXPORT PDF
    public function exportPdf(Request $request)
    {
        $selectedDate = $request->date ?? date('Y-m-d');

        $collections = Collection::with(['user', 'items'])
            ->whereDate('created_at', $selectedDate)
            ->where('branch_id', auth()->user()->branch_id)
            ->latest()
            ->get();

        $pdf = Pdf::loadView('cashier.collection.pdf', compact(
            'collections',
            'selectedDate'
        ))->setPaper('a4', 'landscape');

        return $pdf->download('collection_report_'.$selectedDate.'.pdf');
    }

    // EXPORT EXCEL
    public function exportExcel(Request $request)
    {
        $selectedDate = $request->date ?? date('Y-m-d');

        return Excel::download(
            new CollectionExport($selectedDate, auth()->user()->branch_id),
            'collection_report_'.$selectedDate.'.xlsx'
        );
    }

    // Save Collection Receipt + Deduct Stock + Reflect to Sales
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

                // SAVE COLLECTION HEADER
                $collection = Collection::create([
                    'receipt_no'    => $request->receipt_no,
                    'receipt_date'  => $request->receipt_date,
                    'customer_name' => $request->customer_name ?? '',
                    'address'       => $request->address ?? '',
                    'terms'         => $request->terms ?? '',
                    'total_amount'  => $request->total_amount ?? 0,
                    'branch_id'     => $branchId,
                    'user_id'       => $userId,
                ]);

                // SAVE SALES HEADER
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

                    $product->stock = $product->stock - $qty;
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