@extends('layouts.cashier')

@section('content')

<style>
    .topbar {
        display: flex;
        justify-content: space-between;
        margin-bottom: 15px;
    }

    .search input {
        width: 100%;
        padding: 10px;
        border-radius: 10px;
        border: 1px solid #ddd;
    }

    .section {
        display: none;
    }

    .section.active {
        display: block;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 15px;
        margin-top: 15px;
    }

    .product {
        background: white;
        padding: 15px;
        border-radius: 12px;
        cursor: pointer;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        transition: 0.2s;
    }

    .product:hover {
        transform: scale(1.05);
    }

    .card {
        background: white;
        padding: 20px;
        border-radius: 12px;
    }
</style>

<div class="topbar">
    <h2>Cashier Dashboard</h2>
    <div>👤 {{ auth()->user()->username }}</div>
</div>

<!-- POS -->
<div id="pos" class="section active">

    <div class="search">
        <input type="text" placeholder="Search product...">
    </div>

    <div class="grid">
        @foreach($products as $product)
        <div class="product"
            onclick='addToCart({{ $product->id }}, @json($product->name), {{ $product->price }})'>

            <h4>{{ $product->name }}</h4>
            <p>₱{{ number_format($product->price,2) }}</p>
            <small>Stock: {{ $product->stock }}</small>
        </div>
        @endforeach
    </div>

</div>

<!-- DCCR -->
<div id="dccr" class="section">
    <div class="card">
        <h2>💰 Daily Cash Report</h2>

        <p>Total Sales: ₱<span id="sales">{{ $todaySales ?? 0 }}</span></p>

        <label>Actual Cash</label>
        <input type="number" id="actual">

        <h3>Difference: ₱<span id="diff">0</span></h3>

        <button onclick="compute()">Compute</button>
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

        <button>Submit</button>
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
        padding: 10px;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .total {
        font-size: 20px;
        margin: 10px 0;
    }
</style>

<h2>🛒 Cart</h2>

<div class="cart-items" id="cart"></div>

<div class="total">
    Total: ₱<span id="total">0.00</span>
</div>

<input type="number" id="cash" placeholder="Enter cash">

<div>Change: ₱<span id="change">0.00</span></div>

<button class="pay" id="payBtn" onclick="checkout()" disabled>PAY</button>

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