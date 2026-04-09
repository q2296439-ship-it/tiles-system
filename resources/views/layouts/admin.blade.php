<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            background: #f1f5f9;
        }

        .sidebar {
            width: 240px;
            height: 100vh;
            background: #1e293b;
            color: white;
            padding: 20px;
            position: fixed;
            left: 0;
            top: 0;
            overflow-y: auto;
            box-sizing: border-box;
        }

        .sidebar h2 {
            margin-bottom: 25px;
            white-space: nowrap;
        }

        .menu-title {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 20px;
            margin-bottom: 8px;
        }

        .sidebar a {
            display: block;
            color: #cbd5e1;
            text-decoration: none;
            padding: 6px 0;
            font-size: 14px;
        }

        .sidebar a:hover {
            color: white;
        }

        .sidebar a.active {
            color: white;
            font-weight: bold;
        }

        .logout-btn {
            background: none;
            border: none;
            color: #cbd5e1;
            cursor: pointer;
            text-align: left;
            padding: 0;
            font-size: 14px;
            margin-top: 6px;
        }

        .logout-btn:hover {
            color: white;
        }

        .main {
            margin-left: 240px;
            width: calc(100% - 240px);
        }

        .topbar {
            background: white;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content {
            padding: 20px;
        }

        .card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .kpi { color: white; }

        .blue { background: #3b82f6; }
        .green { background: #22c55e; }
        .yellow { background: #f59e0b; }
        .red { background: #ef4444; }

        .section-title {
            margin-bottom: 10px;
            font-weight: bold;
        }

        .low {
            color: red;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f9fafb;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
    </style>
</head>

<body>

<div class="sidebar">
    <h2>Admin Panel</h2>

    <div class="menu-title">MAIN</div>
    <a href="/admin/dashboard" class="{{ request()->is('admin/dashboard') ? 'active' : '' }}">📊 Dashboard</a>

    <div class="menu-title">POS / SALES</div>
    <a href="/admin/pos" class="{{ request()->is('admin/pos') ? 'active' : '' }}">💰 POS</a>

    <a href="/admin/sales/brand" class="{{ request()->is('admin/sales/brand') ? 'active' : '' }}">
        📊 Per Brand
    </a>

    <a href="/admin/sales/branch" class="{{ request()->is('admin/sales/branch') ? 'active' : '' }}">🏬 Per Branch</a>
    <a href="/admin/sales/daily" class="{{ request()->is('admin/sales/daily') ? 'active' : '' }}">📅 Daily Sales</a>

    <div class="menu-title">PRODUCT</div>
    <a href="/admin/products" class="{{ request()->is('admin/products') ? 'active' : '' }}">📦 Product Overview</a>
    <a href="#">🏷 Per Model</a>

    <div class="menu-title">INVENTORY</div>
    <a href="/admin/inventory" class="{{ request()->is('admin/inventory') ? 'active' : '' }}">📦 Overview Stock</a>

    <a href="{{ route('inventory.create') }}" 
       class="{{ request()->is('admin/inventory/add-stock') ? 'active' : '' }}">
        ➕ Add New Stock
    </a>

    <!-- ✅ FIXED (DIRECT URL) -->
    <a href="/admin/inventory/transfer-in"
       class="{{ request()->is('admin/inventory/transfer-in') ? 'active' : '' }}">
        ⬅ Transfer In
    </a>

    <a href="#">➡ Transfer Out</a>
    <a href="#">📄 Delivery Report</a>

    <div class="menu-title">USER</div>
    <a href="/admin/users">➕ Add User</a>
    <a href="/admin/users">👥 Manage Account</a>

    <a href="/admin/branches" class="{{ request()->is('admin/branches') ? 'active' : '' }}">🏬 Add Branch</a>

    <div class="menu-title">ACCOUNT</div>
    <form method="POST" action="/logout">
        @csrf
        <button type="submit" class="logout-btn">🚪 Logout</button>
    </form>
</div>

<div class="main">

    <div class="topbar">
        <strong>Admin Panel</strong>
        <span>👤 {{ auth()->user()->name ?? 'Admin' }}</span>
    </div>

    <div class="content">
        @yield('content')
    </div>

</div>

</body>
</html>