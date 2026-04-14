<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Collection;
use App\Models\CollectionItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CollectionController extends Controller
{
    // Show Add Collection Receipt Page
    public function create()
    {
        return view('cashier.collection.create');
    }

    // Save Collection Receipt + Deduct Stock
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

                // ✅ Save Header
                $collection = Collection::create([
                    'receipt_no'    => $request->receipt_no,
                    'receipt_date'  => $request->receipt_date,
                    'customer_name' => $request->customer_name ?? '',
                    'address'       => $request->address ?? '',
                    'terms'         => $request->terms ?? '',
                    'total_amount'  => $request->total_amount ?? 0,
                    'branch_id'     => $branchId,
                    'user_id'       => auth()->id(),
                ]);

                foreach ($request->items as $item) {

                    // Skip empty row
                    if (empty($item['description'])) {
                        continue;
                    }

                    $qty   = (int) ($item['qty'] ?? 0);
                    $price = (float) ($item['unit_price'] ?? 0);
                    $amount = $qty * $price;

                    // ✅ Save Item
                    CollectionItem::create([
                        'collection_id' => $collection->id,
                        'qty'           => $qty,
                        'unit'          => $item['unit'] ?? '',
                        'description'   => trim($item['description']),
                        'unit_price'    => $price,
                        'amount'        => $amount,
                    ]);

                    // ✅ Find Product (branch based)
                    $product = Product::where('name', trim($item['description']))
                        ->where('branch_id', $branchId)
                        ->first();

                    if (!$product) {
                        throw new \Exception('Product not found: ' . $item['description']);
                    }

                    if ($product->stock < $qty) {
                        throw new \Exception('Not enough stock: ' . $item['description']);
                    }

                    // ✅ Deduct stock
                    $product->stock = $product->stock - $qty;
                    $product->save();
                }
            });

            return back()->with('success', 'Receipt saved successfully!');

        } catch (\Throwable $e) {

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}