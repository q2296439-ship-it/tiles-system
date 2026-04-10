<!DOCTYPE html>
<html>
<head>
    <title>Cashier POS</title>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            height: 100vh;
            background: #f1f5f9;
        }

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
        }

        .sidebar a:hover {
            background: #1e293b;
        }

        .active {
            background: #22c55e;
            color: white !important;
        }

        .content {
            flex: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .cart {
            width: 320px;
            background: #1e293b;
            color: white;
            padding: 20px;
        }
    </style>
</head>

<body>

<div class="sidebar">
    <h2>💰 POS</h2>

    <a href="/cashier" class="active">🧾 New Sale</a>
    <a href="#">💰 DCCR</a>
    <a href="#">🏦 Deposit</a>

    <form method="POST" action="/logout">
        @csrf
        <button style="margin-top:20px;">Logout</button>
    </form>
</div>

<div class="content">
    @yield('content')
</div>

<div class="cart">
    @yield('cart')
</div>

</body>
</html>