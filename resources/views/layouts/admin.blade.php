<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        *{
            box-sizing:border-box;
            margin:0;
            padding:0;
        }

        html,body{
            width:100%;
            overflow-x:hidden;
        }

        body{
            font-family:'Segoe UI',Tahoma,sans-serif;
            background:#f1f5f9;
            color:#111827;
        }

        a{
            text-decoration:none;
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
            transition:.3s ease;
            z-index:1000;
        }

        .sidebar.hide{
            left:-260px;
        }

        .brand{
            font-size:18px;
            font-weight:800;
            margin-bottom:24px;
            white-space:nowrap;
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
            padding:10px 12px;
            font-size:14px;
            border:none;
            background:none;
            border-radius:10px;
            cursor:pointer;
            margin-bottom:6px;
            transition:.2s;
            white-space:nowrap;
        }

        .sidebar a:hover,
        .logout-btn:hover{
            background:rgba(255,255,255,.08);
            color:#fff;
        }

        .sidebar a.active{
            background:linear-gradient(135deg,#2563eb,#3b82f6);
            color:#fff;
            font-weight:700;
        }

        /* MAIN */
        .main{
            margin-left:250px;
            width:calc(100% - 250px);
            min-height:100vh;
            transition:.3s ease;
        }

        /* TOPBAR */
        .topbar{
            background:#fff;
            border-bottom:1px solid #e5e7eb;
            padding:15px 20px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:15px;
            position:sticky;
            top:0;
            z-index:900;
            width:100%;
        }

        .left-wrap{
            display:flex;
            align-items:center;
            gap:12px;
            min-width:0;
        }

        .menu-btn{
            display:none;
            border:none;
            background:#2563eb;
            color:#fff;
            width:42px;
            height:42px;
            border-radius:10px;
            font-size:18px;
            cursor:pointer;
            flex-shrink:0;
        }

        .top-title{
            font-size:18px;
            font-weight:800;
            line-height:1.2;
        }

        .top-sub{
            font-size:12px;
            color:#6b7280;
            margin-top:2px;
        }

        .user-box{
            display:flex;
            align-items:center;
            gap:10px;
            background:#f8fafc;
            padding:8px 12px;
            border-radius:12px;
            flex-shrink:0;
        }

        .avatar{
            width:34px;
            height:34px;
            border-radius:50%;
            background:#2563eb;
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
        }

        .user-name{
            font-size:14px;
            font-weight:700;
            white-space:nowrap;
        }

        .user-role{
            font-size:11px;
            color:#6b7280;
        }

        /* CONTENT */
        .content{
            padding:20px;
            width:100%;
            max-width:100%;
        }

        /* IMPORTANT FIX FOR DESKTOP */
        .content > *{
            width:100%;
            max-width:100%;
        }

        /* TABLES */
        table{
            width:100%;
            border-collapse:collapse;
        }

        .table-responsive{
            width:100%;
            overflow-x:auto;
        }

        /* OVERLAY */
        .overlay{
            position:fixed;
            inset:0;
            background:rgba(0,0,0,.4);
            display:none;
            z-index:999;
        }

        .overlay.show{
            display:block;
        }

        /* RESPONSIVE */
        @media(max-width:768px){

            .menu-btn{
                display:flex;
                align-items:center;
                justify-content:center;
            }

            .sidebar{
                left:-260px;
            }

            .sidebar.show{
                left:0;
            }

            .main{
                margin-left:0;
                width:100%;
            }

            .topbar{
                padding:14px;
            }

            .content{
                padding:14px;
            }

            .top-title{
                font-size:16px;
            }

            .top-sub{
                display:none;
            }

            .user-info{
                display:none;
            }

            table{
                min-width:700px;
            }
        }

        @media(max-width:480px){

            .content{
                padding:12px;
            }

            .brand{
                font-size:16px;
            }

            .sidebar a{
                font-size:13px;
            }

            .topbar{
                gap:10px;
            }

            .avatar{
                width:32px;
                height:32px;
            }
        }

        ::-webkit-scrollbar{
            width:7px;
            height:7px;
        }

        ::-webkit-scrollbar-thumb{
            background:#94a3b8;
            border-radius:20px;
        }
    </style>
</head>

<body>

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

    <div class="brand">🏢 Admin Panel</div>

    <div class="menu-title">Main</div>
    <a href="/admin/dashboard" class="{{ request()->is('admin') || request()->is('admin/dashboard') ? 'active' : '' }}">📊 Dashboard</a>

    <div class="menu-title">POS / Sales</div>
    <a href="/admin/collection" class="{{ request()->is('admin/collection') ? 'active' : '' }}">💰 Collection</a>
    <a href="/admin/sales/brand" class="{{ request()->is('admin/sales/brand') ? 'active' : '' }}">📈 Per Brand</a>
    <a href="/admin/sales/branch" class="{{ request()->is('admin/sales/branch') ? 'active' : '' }}">🏬 Per Branch</a>
    <a href="/admin/sales/daily" class="{{ request()->is('admin/sales/daily') ? 'active' : '' }}">📅 Daily Sales</a>

    <div class="menu-title">Product</div>
    <a href="/admin/products" class="{{ request()->is('admin/products') ? 'active' : '' }}">📦 Product Overview</a>

    <div class="menu-title">Inventory</div>
    <a href="{{ route('admin.inventory.index') }}" class="{{ request()->is('admin/inventory') ? 'active' : '' }}">📦 Overview Stock</a>
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

    <div class="topbar">

        <div class="left-wrap">
            <button class="menu-btn" onclick="toggleSidebar()">☰</button>

            <div>
                <div class="top-title">Admin Workspace</div>
                <div class="top-sub">Manage reports, products, inventory and users</div>
            </div>
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

    <div class="content">
        @yield('content')
    </div>

</div>

<script>
function toggleSidebar(){
    document.getElementById('sidebar').classList.toggle('show');
    document.getElementById('overlay').classList.toggle('show');
}

function closeSidebar(){
    document.getElementById('sidebar').classList.remove('show');
    document.getElementById('overlay').classList.remove('show');
}
</script>

</body>
</html>