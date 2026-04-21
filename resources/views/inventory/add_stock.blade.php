@php
$layout = match(auth()->user()->role) {
    'admin' => 'layouts.admin',
    'manager' => 'layouts.manager',
    default => 'layouts.cashier',
};
@endphp

@extends($layout)

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
</style>

<div class="card">

    <h2>➕ Add New Stock</h2>

    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

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

        <label>Mode</label>
        <select id="modeSelect">
            <option value="existing">Select Existing Product</option>
            <option value="new">Add New Product</option>
        </select>

        <label>Select Branch</label>
<select name="branch_id" id="branchSelect" required>
    <option value="">-- Select Branch --</option>
    @foreach($branches as $branch)
        <option value="{{ $branch->id }}">
            {{ $branch->name }}
        </option>
    @endforeach
</select>
        {{-- EXISTING PRODUCT --}}
        <div id="existingProduct">

            <label>Product</label>
            <select name="product_id" id="productSelect">
                <option value="">-- Select Product --</option>
                @foreach($products as $product)
                    <option value="{{ $product->id }}"
                        data-stock="{{ $product->stock }}"
                        data-price="{{ $product->price }}">
                        {{ $product->name }} (Stock: {{ $product->stock }})
                    </option>
                @endforeach
            </select>

            <label>Current Stock</label>
            <input type="text" id="currentStock" readonly>

            <label>Update Price</label>
            <input type="number" step="0.01" name="price" id="priceInput">

            <label>D.R Number</label>
            <input type="text" name="dr_number">

        </div>

        {{-- NEW PRODUCT --}}
        <div id="newProduct" style="display:none;">

            <label>Product Name</label>
            <input type="text" name="new_name">

            <label>Size</label>
            <input type="text" name="new_size">

            <label>Price</label>
            <input type="number" step="0.01" name="new_price">

            <label>D.R Number</label>
            <input type="text" name="dr_number_new">

        </div>

        <label>Quantity</label>
        <input type="number" name="quantity" required>

        <button type="submit" class="btn">Save</button>
    </form>

</div>

<script>
const modeSelect = document.getElementById('modeSelect');
const existingDiv = document.getElementById('existingProduct');
const newDiv = document.getElementById('newProduct');

modeSelect.addEventListener('change', function () {
    if (this.value === 'new') {
        existingDiv.style.display = 'none';
        newDiv.style.display = 'block';
    } else {
        existingDiv.style.display = 'block';
        newDiv.style.display = 'none';
    }
});

const productSelect = document.getElementById('productSelect');
const currentStock = document.getElementById('currentStock');
const priceInput = document.getElementById('priceInput');

productSelect.addEventListener('change', function () {
    const selected = this.options[this.selectedIndex];

    currentStock.value = selected.getAttribute('data-stock') || '';
    priceInput.value = selected.getAttribute('data-price') || '';
});
</script>

@endsection