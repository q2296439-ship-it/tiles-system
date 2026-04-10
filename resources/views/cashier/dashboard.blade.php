@extends('layouts.cashier')

@section('content')

<style>
    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .topbar h2 {
        margin: 0;
    }

    .search input {
        width: 100%;
        padding: 12px;
        border-radius: 12px;
        border: 1px solid #ddd;
        font-size: 14px;
    }

    .section {
        display: none;
    }

    .section.active {
        display: block;
    }

    /* 🔥 FIXED GRID */
    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 15px;
        margin-top: 15px;
    }

    /* 🔥 PRODUCT CARD (PRO STYLE) */
    .product {
        background: white;
        padding: 15px;
        border-radius: 16px;
        cursor: pointer;
        text-align: center;
        box-shadow: 0 6px 15px rgba(0,0,0,0.08);
        transition: 0.2s;
        border: 2px solid transparent;
    }

    .product:hover {
        transform: translateY(-5px);
        border-color: #22c55e;
    }

    .product h4 {
        margin: 5px 0;
        font-size: 15px;
    }

    .product p {
        font-weight: bold;
        color: #22c55e;
        margin: 5px 0;
    }

    .product small {
        display: block;
        margin-top: 5px;
        font-size: 12px;
    }

    /* 🔥 OUT OF STOCK */
    .out {
        opacity: 0.5;
        pointer-events: none;
    }

    .card {
        background: white;
        padding: 20px;
        border-radius: 16px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
    }
</style>

<div class="topbar">
    <h2>🧾 Cashier POS</h2>
    <div>👤 {{ auth()->user()->username }}</div>
</div>

<!-- POS -->
<div id="pos" class="section active">

    <div class="search">
        <input type="text" placeholder="🔍 Search product...">
    </div>

    <div class="grid">
        @forelse($products as $product)
        <div class="product {{ $product->stock <= 0 ? 'out' : '' }}"
            onclick='addToCart({{ $product->id }}, @json($product->name), {{ $product->price }})'>

            <h4>{{ $product->name }}</h4>

            <p>₱{{ number_format($product->price,2) }}</p>

            <small style="color: {{ $product->stock <= 5 ? 'red' : '#6b7280' }}">
                Stock: {{ $product->stock }}
            </small>

        </div>
        @empty
        <p>No products found</p>
        @endforelse
    </div>

</div>

<!-- DCCR -->
<div id="dccr" class="section">
    <div class="card">
        <h2>💰 Daily Cash Report</h2>

        <p>Total Sales: ₱<strong id="sales">{{ $todaySales ?? 0 }}</strong></p>

        <label>Actual Cash</label>
        <input type="number" id="actual">

        <h3>Difference: ₱<span id="diff">0</span></h3>

        <button onclick="compute()" style="background:#22c55e;color:white;padding:10px;border:none;border-radius:8px;">
            Compute
        </button>
    </div>
</div>

<!-- DEPOSIT -->
<div id="deposit" class="section">
    <div class="card">
        <h2>🏦 Deposit</h2>

        <label>Amount</label>
        <input type="number">

        <label>Reference</label>
        <input type="text">

        <button style="background:#22c55e;color:white;padding:10px;border:none;border-radius:8px;">
            Submit
        </button>
    </div>
</div>

@endsection


@section('cart')

<style>
    .cart-items {
        flex: 1;
        overflow-y: auto;
    }

    .item {
        background: #334155;
        padding: 12px;
        border-radius: 10px;
        margin-bottom: 10px;
    }

    .total {
        font-size: 22px;
        margin: 10px 0;
        font-weight: bold;
    }

    #cash {
        padding: 12px;
        border-radius: 10px;
        border: none;
        width: 100%;
    }

    .pay {
        width: 100%;
        padding: 15px;
        border-radius: 12px;
        font-size: 16px;
        background: #22c55e;
        color: white;
    }

    .pay:disabled {
        background: gray;
    }
</style>

<h2>🛒 Cart</h2>

<div class="cart-items" id="cart"></div>

<div class="total">
    ₱<span id="total">0.00</span>
</div>

<input type="number" id="cash" placeholder="Enter cash">

<div>Change: ₱<span id="change">0.00</span></div>

<button class="pay" id="payBtn" onclick="checkout()" disabled>
    💳 PAY
</button>

@endsection


@section('scripts')

<script>
function showSection(id){
    document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
    document.getElementById(id).classList.add('active');
}

// DCCR compute
function compute(){
    let actual = parseFloat(document.getElementById('actual').value) || 0;
    let sales = parseFloat(document.getElementById('sales').innerText) || 0;
    document.getElementById('diff').innerText = (actual - sales).toFixed(2);
}
</script>

@endsection