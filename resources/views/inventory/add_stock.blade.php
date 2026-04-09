@extends('layouts.admin')

@section('content')

<div class="card">
    <h2>Add New Stock</h2>

    @if(session('success'))
        <div style="color: green; margin-bottom: 10px;">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.store') }}">
        @csrf

        <div style="margin-bottom: 10px;">
            <label>Product</label>
            <select name="product_id" required style="width:100%; padding:8px;">
                <option value="">Select Product</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}">
                        {{ $product->name }} (Stock: {{ $product->stock }})
                    </option>
                @endforeach
            </select>
        </div>

        <div style="margin-bottom: 10px;">
            <label>Quantity</label>
            <input type="number" name="quantity" required style="width:100%; padding:8px;">
        </div>

        <button type="submit" style="padding:10px 20px; background:#3b82f6; color:white; border:none;">
            Add Stock
        </button>
    </form>
</div>

@endsection