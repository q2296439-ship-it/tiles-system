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
            width: 220px;
            background: linear-gradient(180deg, #0f172a, #020617);
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .sidebar h2 {
            margin-bottom: 20px;
            font-size: 20px;
        }

        .sidebar a {
            display: block;
            padding: 12px;
            color: #cbd5f5;
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 8px;
            transition: 0.2s;
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

        .logout {
            margin-top: auto;
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

        /* SCROLL FIX */
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
    <h2>💰 POS</h2>

    <a href="/cashier" class="active">🧾 New Sale</a>
    <a href="#">💰 DCCR</a>
    <a href="#">🏦 Deposit</a>

    <div class="logout">
        <form method="POST" action="/logout">
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

</body>
</html>