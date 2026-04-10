<!DOCTYPE html>
<html>
<head>
    <title>Manager Panel</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 230px;
            height: 100vh;
            background: #1e293b;
            color: white;
            padding: 20px;
            overflow-y: auto;
        }

        .sidebar h2 {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .sidebar p {
            font-size: 11px;
            color: #94a3b8;
            margin: 18px 0 6px;
            letter-spacing: 1px;
        }

        .sidebar a {
            display: block;
            color: #cbd5f5;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 6px;
            font-size: 14px;
            transition: 0.2s;
        }

        .sidebar a:hover {
            background: #334155;
            color: white;
        }

        .active {
            background: #2563eb;
            color: white !important;
        }

        .content {
            margin-left: 230px;
            padding: 30px;
            background: #f1f5f9;
            min-height: 100vh;
        }

        hr {
            border: none;
            border-top: 1px solid #334155;
            margin: 15px 0;
        }
    </style>
</head>

<body>

<div class="sidebar">
    <h2>Manager Panel</h2>

    {{-- MAIN --}}
    <p>MAIN</p>
    <a href="/manager" class="{{ request()->is('manager') ? 'active' : '' }}">
        📊 Dashboard
    </a>

    {{-- OPERATIONS --}}
    <p>OPERATIONS</p>
    <a href="/admin/manager/approvals" class="{{ request()->is('admin/manager/approvals') ? 'active' : '' }}">
        🧾 Approvals
    </a>

    <a href="/manager/transactions" class="{{ request()->is('manager/transactions') ? 'active' : '' }}">
        💳 Transactions
    </a>

    {{-- SALES --}}
    <p>SALES</p>
    <a href="/manager/daily-sales" class="{{ request()->is('manager/daily-sales') ? 'active' : '' }}">
        📅 Daily Sales
    </a>

    <a href="/manager/sales-report" class="{{ request()->is('manager/sales-report') ? 'active' : '' }}">
        📈 Sales Report
    </a>

    {{-- INVENTORY --}}
    <p>INVENTORY</p>

    <a href="/manager/inventory" class="{{ request()->is('manager/inventory') ? 'active' : '' }}">
        📦 Branch Stock
    </a>

    <a href="/manager/inventory-report" class="{{ request()->is('manager/inventory-report') ? 'active' : '' }}">
        📊 Inventory Report
    </a>

    <a href="/manager/add-stock" class="{{ request()->is('manager/add-stock') ? 'active' : '' }}">
        ➕ Add Stock
    </a>

    <a href="/manager/transfer-in" class="{{ request()->is('manager/transfer-in') ? 'active' : '' }}">
        ⬅️ Transfer In
    </a>

    <a href="/manager/transfer-out" class="{{ request()->is('manager/transfer-out') ? 'active' : '' }}">
        ➡️ Transfer Out
    </a>

    <a href="/manager/transfers" class="{{ request()->is('manager/transfers') ? 'active' : '' }}">
        🔄 Transfer Requests
    </a>

    <hr>

    {{-- ACCOUNT --}}
    <p>ACCOUNT</p>
    <a href="/logout" style="color:#f87171;">
        🚪 Logout
    </a>
</div>

<div class="content">
    @yield('content')
</div>

</body>
</html>