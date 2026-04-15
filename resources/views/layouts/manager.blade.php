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

        .sidebar a,
        .sidebar button {
            display: block;
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            color: #cbd5f5;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 6px;
            margin-bottom: 6px;
            font-size: 14px;
            cursor: pointer;
            transition: 0.2s;
        }

        .sidebar a:hover,
        .sidebar button:hover {
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

        .topbar {
            background: white;
            padding: 20px 25px;
            border-radius: 14px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            flex-wrap: wrap;
        }

        .topbar h1 {
            margin: 0;
            font-size: 32px;
            color: #0f172a;
        }

        .topbar p {
            margin: 6px 0 0;
            color: #64748b;
            font-size: 14px;
        }

        .user-card {
            background: #f8fafc;
            padding: 12px 18px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            background: #2563eb;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        hr {
            border: none;
            border-top: 1px solid #334155;
            margin: 15px 0;
        }

        .logout-btn {
            color: #f87171 !important;
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

    <a href="/manager/approvals"
       class="{{ request()->is('manager/approvals') ? 'active' : '' }}">
        🧾 Approvals
    </a>

    <a href="/manager/request-access"
       class="{{ request()->is('manager/request-access') ? 'active' : '' }}">
        🔓 Request Access
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

    <hr>

    {{-- ACCOUNT --}}
    <p>ACCOUNT</p>

    <a href="/manager/change-password"
       class="{{ request()->is('manager/change-password') ? 'active' : '' }}">
        🔑 Change Password
    </a>

    <form method="POST" action="/logout">
        @csrf
        <button type="submit" class="logout-btn">
            🚪 Logout
        </button>
    </form>
</div>

<div class="content">

    <div class="topbar">
        <div>
            <h1>Manager Workspace</h1>
            <p>Manage approvals, sales, inventory and transfers</p>
        </div>

        <div class="user-card">
            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>

            <div>
                <div style="font-weight:700;">
                    {{ auth()->user()->name }}
                </div>
                <small style="color:#64748b;">Manager</small>
            </div>
        </div>
    </div>

    @yield('content')
</div>

</body>
</html>