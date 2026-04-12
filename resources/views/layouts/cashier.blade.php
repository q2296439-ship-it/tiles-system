<!DOCTYPE html>
<html>
<head>
    <title>Cashier POS</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            height: 100vh;
            background: #f1f5f9;
        }

        /* SIDEBAR */
        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, #0f172a, #020617);
            color: white;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .sidebar h2 {
            margin-bottom: 20px;
            font-size: 20px;
        }

        .sidebar p {
            font-size: 11px;
            color: #94a3b8;
            margin: 18px 0 6px;
            letter-spacing: 1px;
        }

        .sidebar a {
            display: block;
            padding: 12px;
            color: #cbd5f5;
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 6px;
            transition: 0.2s;
            font-size: 14px;
        }

        .sidebar a:hover {
            background: #1e293b;
            color: white;
        }

        .active {
            background: #22c55e;
            color: white !important;
            font-weight: bold;
        }

        hr {
            border: none;
            border-top: 1px solid #334155;
            margin: 15px 0;
        }

        .logout {
            padding: 15px;
        }

        .logout button {
            width: 100%;
            padding: 12px;
            background: #ef4444;
            border: none;
            border-radius: 10px;
            color: white;
            cursor: pointer;
        }

        .sidebar-menu::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 10px;
        }

        /* CONTENT */
        .content {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
        }

        /* CART */
        .cart {
            width: 320px;
            background: linear-gradient(180deg, #1e293b, #020617);
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .cart h2 {
            margin-bottom: 15px;
        }

        .content::-webkit-scrollbar,
        .cart::-webkit-scrollbar {
            width: 6px;
        }

        .content::-webkit-scrollbar-thumb,
        .cart::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border-radius: 10px;
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="sidebar-menu">

        <h2>💰 POS</h2>

        <!-- MAIN -->
        <p>MAIN</p>
        <a href="{{ url('/cashier') }}" 
           class="{{ request()->is('cashier') ? 'active' : '' }}">
            🧾 New Sale
        </a>

        <!-- SALES -->
        <p>SALES</p>
        <a href="#">🧾 Add Collection Receipt</a>
        <a href="#">📊 Collection Today</a>
        <a href="#">💰 DCCR</a>
        <a href="#">🏦 Deposit</a>

        <!-- INVENTORY -->
        <p>INVENTORY</p>
        <a href="#">📦 Inventory Stock</a>

        <!-- ✅ FIXED TRANSFER IN -->
        <a href="{{ route('cashier.transfer.in') }}" 
           class="{{ request()->is('cashier/transfer-in*') ? 'active' : '' }}">
            ⬇ Transfer In
        </a>

        <!-- FUTURE -->
        <a href="#">⬆ Transfer Out</a>

        <hr>

        <!-- ACCOUNT -->
        <p>ACCOUNT</p>
        <a href="#">🔑 Change Password</a>

    </div>

    <div class="logout">
        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button>🚪 Logout</button>
        </form>
    </div>

</div>

<!-- CONTENT -->
<div class="content">
    @yield('content')
</div>

<!-- CART -->
<div class="cart">
    @yield('cart')
</div>

<!-- SCRIPTS -->
@yield('scripts')

</body>
</html>