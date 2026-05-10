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

    input,
    select {
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

    .select2-container .select2-selection--single {
        height: 42px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        padding-top: 5px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px !important;
    }
</style>

<div class="card">

    <h2>➕ Add New Stock</h2>

    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="error">{{ session('error') }}</div>
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

    <form method="POST" action="{{ route('stock.store') }}">
        @csrf

        <label>Mode</label>
        <select id="modeSelect" name="mode">
            <option value="existing">Select Existing Product</option>
            <option value="new">Add New Product</option>
        </select>

        <label>Select Branch</label>

        @if(strtolower(auth()->user()->role) === 'cashier')

            <input type="text"
                   value="{{ auth()->user()->branch->name ?? 'Assigned Branch' }}"
                   readonly>

            <input type="hidden"
                   name="branch_id"
                   id="branchSelect"
                   value="{{ auth()->user()->branch_id }}">

        @else

            <select name="branch_id" id="branchSelect" required>
                <option value="">-- Select Branch --</option>

                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>

        @endif

        {{-- EXISTING PRODUCT --}}
        <div id="existingProduct">

            <label>Product</label>

            <select name="product_id" id="productSelect">
                <option value="">-- Search Product --</option>

                @foreach($products as $product)

                    <option value="{{ $product->id }}"
                        data-stock="{{ $product->stock }}"
                        data-price="{{ $product->price }}"
                        data-branch="{{ $product->branch_id }}">

                        {{ $product->name }} (Stock: {{ $product->stock }})

                    </option>

                @endforeach
            </select>

            <label>Current Stock</label>
            <input type="text" id="currentStock" readonly>

            <label>Update Price</label>

            @if(strtolower(auth()->user()->role) === 'cashier')

                <input type="number"
                       step="0.01"
                       name="price"
                       id="priceInput"
                       readonly>

            @else

                <input type="number"
                       step="0.01"
                       name="price"
                       id="priceInput">

            @endif

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

            @if(strtolower(auth()->user()->role) === 'cashier')

                <input type="number"
                       step="0.01"
                       value="0"
                       readonly>

                <input type="hidden"
                       name="new_price"
                       value="0">

            @else

                <input type="number"
                       step="0.01"
                       name="new_price">

            @endif

            <label>D.R Number</label>
            <input type="text" name="dr_number_new">

        </div>

        <label>Quantity</label>
        <input type="number" name="quantity" required>

        <button type="submit" class="btn">Save</button>

    </form>

</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>

const modeSelect    = document.getElementById('modeSelect');
const existingDiv   = document.getElementById('existingProduct');
const newDiv        = document.getElementById('newProduct');

const branchSelect  = document.getElementById('branchSelect');
const productSelect = document.getElementById('productSelect');
const currentStock  = document.getElementById('currentStock');
const priceInput    = document.getElementById('priceInput');

const existingFields = existingDiv.querySelectorAll('input, select');
const newFields      = newDiv.querySelectorAll('input, select');

function toggleMode() {

    if (modeSelect.value === 'new') {

        existingDiv.style.display = 'none';
        newDiv.style.display = 'block';

        existingFields.forEach(el => el.disabled = true);
        newFields.forEach(el => el.disabled = false);

    } else {

        existingDiv.style.display = 'block';
        newDiv.style.display = 'none';

        existingFields.forEach(el => el.disabled = false);
        newFields.forEach(el => el.disabled = true);
    }
}

modeSelect.addEventListener('change', toggleMode);

// Save original products
const allProducts = [];

for (let i = 1; i < productSelect.options.length; i++) {

    const opt = productSelect.options[i];

    allProducts.push({
        value: opt.value,
        text: opt.text,
        stock: opt.dataset.stock,
        price: opt.dataset.price,
        branch: opt.dataset.branch
    });
}

// Filter products by branch
branchSelect.addEventListener('change', function () {

    const selectedBranch = this.value;

    productSelect.innerHTML =
        '<option value="">-- Search Product --</option>';

    currentStock.value = '';

    if (priceInput) {
        priceInput.value = '';
    }

    allProducts.forEach(item => {

        if (item.branch === selectedBranch) {

            const option = document.createElement('option');

            option.value = item.value;
            option.textContent = item.text;

            option.dataset.stock = item.stock;
            option.dataset.price = item.price;
            option.dataset.branch = item.branch;

            productSelect.appendChild(option);
        }
    });

    $('#productSelect').select2({
        placeholder: 'Search Product',
        allowClear: true,
        width: '100%'
    });
});

// Product select autofill
productSelect.addEventListener('change', function () {

    const selected = this.options[this.selectedIndex];

    currentStock.value = selected.dataset.stock || '';

    if (priceInput) {
        priceInput.value = selected.dataset.price || '';
    }
});

// init
toggleMode();

$('#productSelect').select2({
    placeholder: 'Search Product',
    allowClear: true,
    width: '100%'
});

if (branchSelect.value) {
    branchSelect.dispatchEvent(new Event('change'));
}

</script>

@endsection