<!DOCTYPE html>
<html>
<head>
    <title>Cashier POS</title>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f1f5f9;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 220px;
            background: #0f172a;
            color: white;
            padding: 20px;
        }

        .sidebar h2 {
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            padding: 10px;
            color: #cbd5f5;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 5px;
            cursor: pointer;
        }

        .sidebar a:hover {
            background: #1e293b;
        }

        .active {
            background: #22c55e;
            color: white !important;
        }

        /* MAIN */
        .main {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

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

        /* PRODUCTS */
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

        /* CART */
        .cart {
            width: 320px;
            background: #1e293b;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

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

        input {
            padding: 10px;
            border-radius: 8px;
            border: none;
            margin-bottom: 10px;
            width: 100%;
        }

        button {
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .pay {
            background: #22c55e;
            color: white;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
        }
    </style>
</head>

<body>

<div class="container">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>💰 POS</h2>

        <a onclick="showSection('pos')" class="active">🧾 New Sale</a>
        <a onclick="showSection('dccr')">💰 DCCR</a>
        <a onclick="showSection('deposit')">🏦 Deposit</a>

        <form method="POST" action="/logout">
            @csrf
            <button style="margin-top:20px; width:100%; background:#ef4444; color:white;">
                Logout
            </button>
        </form>
    </div>

    <!-- MAIN -->
    <div class="main">

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
                    onclick="addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }})">

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

    </div>

    <!-- CART -->
    <div class="cart">

        <h2>🛒 Cart</h2>

        <div class="cart-items" id="cart"></div>

        <div class="total">
            Total: ₱<span id="total">0.00</span>
        </div>

        <input type="number" id="cash" placeholder="Enter cash">

        <div>Change: ₱<span id="change">0.00</span></div>

        <button class="pay" id="payBtn" onclick="checkout()" disabled>PAY</button>

    </div>

</div>

<script>
function showSection(id){
    document.querySelectorAll('.section').forEach(s=>s.classList.remove('active'));
    document.getElementById(id).classList.add('active');

    document.querySelectorAll('.sidebar a').forEach(a=>a.classList.remove('active'));
    event.target.classList.add('active');
}

// DCCR compute
function compute(){
    let actual = parseFloat(document.getElementById('actual').value) || 0;
    let sales = parseFloat(document.getElementById('sales').innerText) || 0;
    document.getElementById('diff').innerText = (actual - sales).toFixed(2);
}
</script>

</body>
</html>