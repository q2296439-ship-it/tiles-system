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
        $request->validate([
            'receipt_no'    => 'required|unique:collections,receipt_no',
            'receipt_date'  => 'required|date',
            'customer_name' => 'nullable|string',
            'items'         => 'required|array|min:1',
        ]);

        try {

            DB::transaction(function () use ($request) {

                $branchId = auth()->user()->branch_id;

                $collection = Collection::create([
                    'receipt_no'    => $request->receipt_no,
                    'receipt_date'  => $request->receipt_date,
                    'customer_name' => $request->customer_name,
                    'address'       => $request->address,
                    'terms'         => $request->terms,
                    'total_amount'  => $request->total_amount,
                    'branch_id'     => $branchId,
                    'user_id'       => auth()->id(),
                ]);

                foreach ($request->items as $item) {

                    CollectionItem::create([
                        'collection_id' => $collection->id,
                        'qty'           => $item['qty'],
                        'unit'          => $item['unit'],
                        'description'   => $item['description'],
                        'unit_price'    => $item['unit_price'],
                        'amount'        => $item['amount'],
                    ]);

                    // 🔥 Find product by description in same branch
                    $product = Product::where('name', $item['description'])
                        ->where('branch_id', $branchId)
                        ->first();

                    if (!$product) {
                        throw new \Exception('Product not found: ' . $item['description']);
                    }

                    if ($product->stock < $item['qty']) {
                        throw new \Exception('Insufficient stock for: ' . $item['description']);
                    }

                    // 🔻 Deduct stock
                    $product->stock -= $item['qty'];
                    $product->save();
                }
            });

            return back()->with('success', 'Collection receipt saved and stock updated!');

        } catch (\Exception $e) {

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}