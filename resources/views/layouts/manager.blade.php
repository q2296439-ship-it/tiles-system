<!DOCTYPE html>
<html>
<head>
    <title>Manager Panel</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 230px;
            background: #1e293b;
            color: white;
            padding: 20px;
        }

        .sidebar h2 {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .sidebar p {
            font-size: 11px;
            color: #94a3b8;
            margin: 15px 0 5px;
        }

        .sidebar a {
            display: block;
            color: #cbd5f5;
            text-decoration: none;
            padding: 8px 12px;
            border-radius: 6px;
            margin-bottom: 5px;
            font-size: 14px;
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
            flex: 1;
            padding: 25px;
            background: #f1f5f9;
        }
    </style>
</head>

<body>

<div class="sidebar">
    <h2>Manager Panel</h2>

    <p>MAIN</p>
    <a href="/manager" class="{{ request()->is('manager') ? 'active' : '' }}">📊 Dashboard</a>

    <p>OPERATIONS</p>
    <a href="/admin/manager/approvals" class="{{ request()->is('admin/manager/approvals') ? 'active' : '' }}">🧾 Approvals</a>
    <a href="/manager/transactions" class="{{ request()->is('manager/transactions') ? 'active' : '' }}">💳 Transactions</a>

    <p>SALES</p>
    <a href="/manager/daily-sales" class="{{ request()->is('manager/daily-sales') ? 'active' : '' }}">📅 Daily Sales</a>
    <a href="/manager/sales-report" class="{{ request()->is('manager/sales-report') ? 'active' : '' }}">📈 Sales Report</a>

    <p>INVENTORY</p>
    <a href="/manager/inventory" class="{{ request()->is('manager/inventory') ? 'active' : '' }}">📦 Branch Stock</a>
    <a href="/manager/inventory-report" class="{{ request()->is('manager/inventory-report') ? 'active' : '' }}">📊 Inventory Report</a>
    <a href="/manager/transfers" class="{{ request()->is('manager/transfers') ? 'active' : '' }}">🔄 Transfer Requests</a>

    <hr style="border-color:#334155; margin:15px 0;">

    <p>ACCOUNT</p>
    <a href="/logout" style="color:#f87171;">🚪 Logout</a>
</div>

<div class="content">
    @yield('content')
</div>

</body>
</html>