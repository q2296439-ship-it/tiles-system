<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        *{
            box-sizing:border-box;
        }

        body{
            margin:0;
            font-family:'Segoe UI',Tahoma,sans-serif;
            display:flex;
            background:#f1f5f9;
            color:#111827;
        }

        /* SIDEBAR */
        .sidebar{
            width:250px;
            height:100vh;
            background:linear-gradient(180deg,#0f172a,#1e293b);
            color:#fff;
            padding:22px 18px;
            position:fixed;
            left:0;
            top:0;
            overflow-y:auto;
        }

        .brand{
            font-size:18px;
            font-weight:800;
            margin-bottom:24px;
            letter-spacing:.3px;
        }

        .menu-title{
            font-size:11px;
            color:#94a3b8;
            margin:20px 0 8px;
            letter-spacing:1px;
            text-transform:uppercase;
        }

        .sidebar a,
        .logout-btn{
            display:flex;
            align-items:center;
            gap:10px;
            width:100%;
            color:#cbd5e1;
            text-decoration:none;
            padding:10px 12px;
            font-size:14px;
            border-radius:10px;
            margin-bottom:6px;
            transition:.2s ease;
            border:none;
            background:none;
            cursor:pointer;
            text-align:left;
        }

        .sidebar a:hover,
        .logout-btn:hover{
            color:#fff;
            background:rgba(255,255,255,0.08);
            transform:translateX(2px);
        }

        .sidebar a.active{
            color:#fff;
            font-weight:700;
            background:linear-gradient(135deg,#2563eb,#3b82f6);
            box-shadow:0 8px 20px rgba(37,99,235,.35);
        }

        /* MAIN */
        .main{
            margin-left:250px;
            width:calc(100% - 250px);
            min-height:100vh;
        }

        /* TOPBAR */
        .topbar{
            background:rgba(255,255,255,.9);
            backdrop-filter:blur(10px);
            padding:16px 26px;
            border-bottom:1px solid #e5e7eb;
            display:flex;
            justify-content:space-between;
            align-items:center;
            position:sticky;
            top:0;
            z-index:99;
        }

        .top-left{
            display:flex;
            flex-direction:column;
            gap:2px;
        }

        .top-title{
            font-size:18px;
            font-weight:800;
            color:#111827;
        }

        .top-sub{
            font-size:12px;
            color:#6b7280;
        }

        .user-box{
            display:flex;
            align-items:center;
            gap:10px;
            background:#fff;
            padding:8px 14px;
            border-radius:12px;
            box-shadow:0 4px 14px rgba(0,0,0,.06);
        }

        .avatar{
            width:34px;
            height:34px;
            border-radius:50%;
            background:linear-gradient(135deg,#2563eb,#3b82f6);
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:14px;
            font-weight:700;
        }

        .user-info{
            line-height:1.2;
        }

        .user-name{
            font-size:14px;
            font-weight:700;
            color:#111827;
        }

        .user-role{
            font-size:11px;
            color:#6b7280;
        }

        /* CONTENT */
        .content{
            padding:22px;
        }

        /* SCROLLBAR */
        ::-webkit-scrollbar{
            width:8px;
        }

        ::-webkit-scrollbar-thumb{
            background:#94a3b8;
            border-radius:20px;
        }

        ::-webkit-scrollbar-track{
            background:transparent;
        }

        /* MOBILE */
        @media(max-width:900px){
            .sidebar{
                width:220px;
            }

            .main{
                margin-left:220px;
                width:calc(100% - 220px);
            }
        }

        @media(max-width:768px){
            .sidebar{
                position:relative;
                width:100%;
                height:auto;
            }

            body{
                display:block;
            }

            .main{
                margin-left:0;
                width:100%;
            }

            .topbar{
                padding:14px 18px;
            }

            .content{
                padding:16px;
            }
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">

    <div class="brand">🏢 Admin Panel</div>

    <div class="menu-title">Main</div>
    <a href="/admin/dashboard" class="{{ request()->is('admin') || request()->is('admin/dashboard') ? 'active' : '' }}">📊 Dashboard</a>

    <div class="menu-title">POS / Sales</div>
    <a href="/admin/pos" class="{{ request()->is('admin/pos') ? 'active' : '' }}">💰 POS</a>
    <a href="/admin/sales/brand" class="{{ request()->is('admin/sales/brand') ? 'active' : '' }}">📈 Per Brand</a>
    <a href="/admin/sales/branch" class="{{ request()->is('admin/sales/branch') ? 'active' : '' }}">🏬 Per Branch</a>
    <a href="/admin/sales/daily" class="{{ request()->is('admin/sales/daily') ? 'active' : '' }}">📅 Daily Sales</a>

    <div class="menu-title">Product</div>
    <a href="/admin/products" class="{{ request()->is('admin/products') ? 'active' : '' }}">📦 Product Overview</a>

    <div class="menu-title">Inventory</div>
    <a href="/admin/inventory" class="{{ request()->is('admin/inventory') ? 'active' : '' }}">📦 Overview Stock</a>
    <a href="{{ route('inventory.create') }}" class="{{ request()->is('admin/inventory/add-stock') ? 'active' : '' }}">➕ Add New Stock</a>
    <a href="/admin/inventory/transfer-out" class="{{ request()->is('admin/inventory/transfer-out') ? 'active' : '' }}">🔄 Transfer Out</a>
    <a href="/admin/inventory/transfer-in" class="{{ request()->is('admin/inventory/transfer-in') ? 'active' : '' }}">📥 Transfer In</a>

    <div class="menu-title">User</div>
    <a href="/admin/users" class="{{ request()->is('admin/users') ? 'active' : '' }}">➕ Add User</a>
    <a href="/admin/manage" class="{{ request()->is('admin/manage') ? 'active' : '' }}">👥 Manage Account</a>
    <a href="/admin/branches" class="{{ request()->is('admin/branches') ? 'active' : '' }}">🏬 Add Branch</a>

    <div class="menu-title">Account</div>
    <form method="POST" action="/logout">
        @csrf
        <button type="submit" class="logout-btn">🚪 Logout</button>
    </form>

</div>

<!-- MAIN -->
<div class="main">

    {{-- Hide topbar on dashboard only --}}
    @if(!request()->is('admin') && !request()->is('admin/dashboard'))
    <div class="topbar">

        <div class="top-left">
            <div class="top-title">Admin Workspace</div>
            <div class="top-sub">Manage reports, products, inventory and users</div>
        </div>

        <div class="user-box">
            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->name ?? 'A',0,1)) }}
            </div>

            <div class="user-info">
                <div class="user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
                <div class="user-role">Administrator</div>
            </div>
        </div>

    </div>
    @endif

    <div class="content">
        @yield('content')
    </div>

</div>

</body>
</html>