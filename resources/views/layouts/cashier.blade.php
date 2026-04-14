<!DOCTYPE html>
<html>
<head>
    <title>Cashier POS</title>

    <style>
        *{
            box-sizing:border-box;
        }

        body{
            margin:0;
            font-family:'Segoe UI',sans-serif;
            display:flex;
            height:100vh;
            background:#f1f5f9;
        }

        .sidebar{
            width:240px;
            background:linear-gradient(180deg,#0f172a,#111827,#020617);
            color:#fff;
            display:flex;
            flex-direction:column;
            height:100vh;
            box-shadow:4px 0 18px rgba(0,0,0,0.08);
            flex-shrink:0;
        }

        .sidebar-menu{
            flex:1;
            overflow-y:auto;
            padding:18px 16px;
        }

        .sidebar h2{
            margin:8px 8px 22px;
            font-size:16px;
            font-weight:800;
            letter-spacing:.3px;
        }

        .sidebar p{
            font-size:11px;
            color:#94a3b8;
            margin:18px 8px 8px;
            letter-spacing:1.2px;
            text-transform:uppercase;
        }

        .sidebar a{
            display:block;
            padding:12px 14px;
            margin-bottom:8px;
            border-radius:12px;
            color:#e5e7eb;
            text-decoration:none;
            font-size:14px;
            transition:all .2s ease;
        }

        .sidebar a:hover{
            background:rgba(59,130,246,.12);
            color:#fff;
            transform:translateX(2px);
        }

        .sidebar a.active{
            background:linear-gradient(90deg,#2563eb,#3b82f6);
            color:#fff !important;
            font-weight:700;
            box-shadow:0 8px 18px rgba(37,99,235,.25);
        }

        hr{
            border:none;
            border-top:1px solid rgba(148,163,184,.18);
            margin:14px 8px;
        }

        .sidebar-menu::-webkit-scrollbar{
            width:6px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb{
            background:#475569;
            border-radius:10px;
        }

        .logout{
            padding:14px 16px;
        }

        .logout button{
            width:100%;
            border:none;
            padding:12px;
            border-radius:12px;
            background:#ef4444;
            color:#fff;
            font-size:14px;
            font-weight:700;
            cursor:pointer;
            transition:.2s;
        }

        .logout button:hover{
            background:#dc2626;
        }

        .content{
            flex:1;
            padding:20px;
            overflow-y:auto;
            min-width:0;
        }

        .content::-webkit-scrollbar{
            width:6px;
        }

        .content::-webkit-scrollbar-thumb{
            background:#94a3b8;
            border-radius:10px;
        }

        .topbar{
            background:#ffffff;
            border-radius:16px;
            padding:18px 24px;
            margin-bottom:20px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            box-shadow:0 6px 18px rgba(0,0,0,0.05);
            gap:15px;
            flex-wrap:wrap;
        }

        .topbar-left h1{
            margin:0;
            font-size:18px;
            font-weight:800;
            color:#111827;
        }

        .topbar-left p{
            margin:4px 0 0;
            font-size:13px;
            color:#6b7280;
        }

        .user-box{
            display:flex;
            align-items:center;
            gap:12px;
            background:#f8fafc;
            padding:8px 14px;
            border-radius:14px;
        }

        .avatar{
            width:38px;
            height:38px;
            border-radius:50%;
            background:#2563eb;
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
        }

        .user-info strong{
            display:block;
            font-size:14px;
            color:#111827;
        }

        .user-info span{
            font-size:12px;
            color:#6b7280;
        }

        .cart{
            width:320px;
            background:linear-gradient(180deg,#1e293b,#020617);
            color:#fff;
            padding:20px;
            display:flex;
            flex-direction:column;
            overflow-y:auto;
            flex-shrink:0;
        }

        .cart h2{
            margin-bottom:15px;
        }

        .cart::-webkit-scrollbar{
            width:6px;
        }

        .cart::-webkit-scrollbar-thumb{
            background:#64748b;
            border-radius:10px;
        }
    </style>
</head>

<body>

<div class="sidebar">

    <div class="sidebar-menu">

        <h2>💰 Cashier Panel</h2>

        <p>Main</p>
        <a href="{{ url('/cashier') }}"
           class="{{ request()->is('cashier') ? 'active' : '' }}">
            🧾 New Sale
        </a>

        <p>Sales</p>

        <a href="{{ route('cashier.collection.create') }}"
           class="{{ request()->is('cashier/collection-receipt*') ? 'active' : '' }}">
            🧾 Add Collection Receipt
        </a>

        <a href="{{ route('cashier.collection.cancel') }}"
           class="{{ request()->is('cashier/collection-cancel*') ? 'active' : '' }}">
            ❌ Cancel Receipt
        </a>

        <a href="{{ route('cashier.return.create') }}"
           class="{{ request()->is('cashier/return-receipt*') ? 'active' : '' }}">
            ↩ Return Receipt
        </a>

        <a href="{{ url('/cashier/collection-today') }}"
           class="{{ request()->is('cashier/collection-today*') ? 'active' : '' }}">
            📊 Collection Today
        </a>

        <a href="{{ url('/cashier/dccr') }}"
           class="{{ request()->is('cashier/dccr*') ? 'active' : '' }}">
            💰 DCCR
        </a>

        <a href="{{ url('/cashier/deposit') }}"
           class="{{ request()->is('cashier/deposit*') ? 'active' : '' }}">
            🏦 Deposit
        </a>

        <p>Inventory</p>

        <a href="{{ route('cashier.inventory.stock') }}"
           class="{{ request()->is('cashier/inventory-stock') ? 'active' : '' }}">
            📦 Inventory Stock
        </a>

        <a href="{{ route('cashier.transfer.in') }}"
           class="{{ request()->is('cashier/transfer-in*') ? 'active' : '' }}">
            ⬇ Transfer In
        </a>

        <a href="{{ url('/cashier/incoming') }}"
           class="{{ request()->is('cashier/incoming*') ? 'active' : '' }}">
            📦 Incoming Transfer Stock
        </a>

        <hr>

        <p>Account</p>
        <a href="#">🔑 Change Password</a>

    </div>

    <div class="logout">
        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button>🚪 Logout</button>
        </form>
    </div>

</div>

<div class="content">

    <div class="topbar">
        <div class="topbar-left">
            <h1>Cashier Workspace</h1>
            <p>Manage collections, sales, inventory and transfers</p>
        </div>

        <div class="user-box">
            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->name,0,1)) }}
            </div>

            <div class="user-info">
                <strong>{{ auth()->user()->name }}</strong>
                <span>Cashier</span>
            </div>
        </div>
    </div>

    @yield('content')

</div>

@if(View::hasSection('cart'))
<div class="cart">
    @yield('cart')
</div>
@endif

@yield('scripts')

</body>
</html>