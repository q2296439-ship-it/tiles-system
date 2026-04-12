<!DOCTYPE html>
<html>
<head>
    <title>Cashier POS</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            height: 100vh;
            background: #f1f5f9;
        }

        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, #0f172a, #020617);
            color: white;
            display: flex;
            flex-direction: column;
        }

        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .sidebar h2 { margin-bottom: 20px; }

        .sidebar p {
            font-size: 11px;
            color: #94a3b8;
            margin: 18px 0 6px;
        }

        .sidebar a {
            display: block;
            padding: 12px;
            color: #cbd5f5;
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 6px;
        }

        .sidebar a:hover { background: #1e293b; }

        .active {
            background: #22c55e;
            color: white !important;
        }

        .logout { padding: 15px; }

        .content {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
        }
    </style>
</head>

<body>

<div class="sidebar">
    <div class="sidebar-menu">

        <h2>💰 POS</h2>

        <p>MAIN</p>
        <a href="{{ url('/cashier') }}" class="{{ request()->is('cashier') ? 'active' : '' }}">
            🧾 New Sale
        </a>

        <p>INVENTORY</p>
        <a href="{{ route('cashier.transfer.in') }}" 
           class="{{ request()->is('cashier/transfer-in*') ? 'active' : '' }}">
            ⬇ Transfer In
        </a>

    </div>

    <div class="logout">
        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button>Logout</button>
        </form>
    </div>
</div>

<div class="content">
    @yield('content')
</div>

</body>
</html>