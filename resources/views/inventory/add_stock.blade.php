@extends('layouts.admin')

@section('content')

<style>
    .card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        max-width: 500px;
        margin: auto;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    h2 {
        margin-bottom: 20px;
    }

    label {
        font-size: 13px;
        color: #475569;
        display: block;
        margin-bottom: 5px;
    }

    input, select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 14px;
    }

    .btn {
        background: #3b82f6;
        color: white;
        padding: 10px;
        border: none;
        width: 100%;
        border-radius: 6px;
        cursor: pointer;
    }

    .error {
        background: #fee2e2;
        color: #991b1b;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 15px;
    }

    .success {
        background: #dcfce7;
        color: #166534;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 15px;
    }

    .back {
        display: inline-block;
        margin-bottom: 15px;
        font-size: 13px;
        text-decoration: none;
        color: #3b82f6;
    }
</style>

<div class="card">

    <a href="/admin/inventory" class="back">← Back to Inventory</a>

    <h2>➕ Add New Stock</h2>

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERRORS --}}
    @if($errors->any())
        <div class="error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.store') }}">
        @csrf

        {{-- ✅ NEW: BRANCH --}}
        <label>Select Branch</label>
        <select name="branch_id" required>
            <option value="">-- Select Branch --</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>

        {{-- PRODUCT --}}
        <label>Product</label>
        <select name="product_id" id="productSelect" required>
            <option value="">-- Select Product --</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}"
                        data-stock="{{ $product->stock }}">
                    {{ $product->name }} (Stock: {{ $product->stock }})
                </option>
            @endforeach
        </select>

        {{-- CURRENT STOCK --}}
        <label>Current Stock</label>
        <input type="text" id="currentStock" readonly>

        {{-- QUANTITY --}}
        <label>Quantity to Add</label>
        <input type="number" name="quantity" required>

        <button type="submit" class="btn">Add Stock</button>
    </form>

</div>

<script>
    const productSelect = document.getElementById('productSelect');
    const currentStock = document.getElementById('currentStock');

    productSelect.addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        const stock = selected.getAttribute('data-stock');
        currentStock.value = stock ?? '';
    });
</script>

@endsection