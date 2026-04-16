<!DOCTYPE html>
<html>
<head>
    <title>{{ auth()->user()->role == 'audit' ? 'Audit Panel' : 'Manager Panel' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <style>
        *{
            box-sizing:border-box;
            margin:0;
            padding:0;
        }

        body{
            font-family:'Segoe UI',Arial,sans-serif;
            background:#f1f5f9;
            color:#111827;
        }

        a{text-decoration:none;}

        /* SIDEBAR */
        .sidebar{
            position:fixed;
            top:0;
            left:0;
            width:230px;
            height:100vh;
            background:#1e293b;
            color:#fff;
            padding:18px 14px;
            overflow-y:auto;
            z-index:1000;
            transition:.3s ease;
        }

        .sidebar.show{
            left:0;
        }

        .sidebar h2{
            font-size:18px;
            margin-bottom:18px;
            font-weight:800;
        }

        .sidebar p{
            font-size:11px;
            color:#94a3b8;
            margin:18px 0 8px;
            letter-spacing:1px;
            text-transform:uppercase;
        }

        .sidebar a,
        .sidebar button{
            display:block;
            width:100%;
            text-align:left;
            border:none;
            background:none;
            color:#cbd5e1;
            padding:10px 12px;
            border-radius:10px;
            margin-bottom:6px;
            font-size:14px;
            cursor:pointer;
            transition:.2s;
        }

        .sidebar a:hover,
        .sidebar button:hover{
            background:#334155;
            color:#fff;
        }

        .active{
            background:#2563eb;
            color:#fff !important;
            font-weight:700;
        }

        .logout-btn{
            color:#f87171 !important;
        }

        hr{
            border:none;
            border-top:1px solid rgba(255,255,255,.08);
            margin:14px 0;
        }

        /* MAIN */
        .main{
            margin-left:230px;
            min-height:100vh;
            transition:.3s ease;
        }

        /* TOPBAR */
        .topbar{
            background:#fff;
            border-bottom:1px solid #e5e7eb;
            padding:16px 18px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:15px;
            position:sticky;
            top:0;
            z-index:900;
            flex-wrap:wrap;
        }

        .left-wrap{
            display:flex;
            align-items:center;
            gap:12px;
        }

        .menu-btn{
            display:none;
            width:40px;
            height:40px;
            border:none;
            border-radius:10px;
            background:#2563eb;
            color:#fff;
            font-size:18px;
            cursor:pointer;
        }

        .topbar h1{
            font-size:28px;
            color:#0f172a;
            margin:0;
        }

        .topbar p{
            margin-top:4px;
            color:#64748b;
            font-size:13px;
        }

        .user-card{
            background:#f8fafc;
            padding:10px 14px;
            border-radius:14px;
            display:flex;
            align-items:center;
            gap:12px;
        }

        .avatar{
            width:40px;
            height:40px;
            border-radius:50%;
            background:#2563eb;
            color:#fff;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
        }

        .user-name{
            font-weight:700;
            font-size:14px;
        }

        .user-role{
            font-size:11px;
            color:#64748b;
        }

        /* CONTENT */
        .content{
            padding:20px;
        }

        /* OVERLAY */
        .overlay{
            position:fixed;
            inset:0;
            background:rgba(0,0,0,.45);
            display:none;
            z-index:999;
        }

        .overlay.show{
            display:block;
        }

        /* TABLES */
        table{
            width:100%;
            display:block;
            overflow-x:auto;
        }

        /* MOBILE */
        @media(max-width:768px){

            .menu-btn{
                display:block;
            }

            .sidebar{
                left:-240px;
            }

            .sidebar.show{
                left:0;
            }

            .main{
                margin-left:0;
            }

            .topbar{
                padding:14px;
            }

            .topbar h1{
                font-size:20px;
            }

            .topbar p,
            .user-meta{
                display:none;
            }

            .content{
                padding:14px;
            }
        }

        @media(max-width:480px){

            .content{
                padding:12px;
            }

            .sidebar a,
            .sidebar button{
                font-size:13px;
            }

            .topbar h1{
                font-size:18px;
            }
        }
    </style>
</head>

<body>

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">

    <h2>{{ auth()->user()->role == 'audit' ? 'Audit Panel' : 'Manager Panel' }}</h2>

    <p>Main</p>

    <a href="/manager" class="{{ request()->is('manager') ? 'active' : '' }}">
        📊 Dashboard
    </a>

    <p>Operations</p>

    @if(auth()->user()->role != 'audit')
    <a href="/manager/approvals" class="{{ request()->is('manager/approvals') ? 'active' : '' }}">
        🧾 Approvals
    </a>
    @endif

    <a href="/manager/request-access" class="{{ request()->is('manager/request-access') ? 'active' : '' }}">
        🔓 Request Access
    </a>

    <a href="/manager/deposit" class="{{ request()->is('manager/deposit') ? 'active' : '' }}">
        💰 Deposit
    </a>

    <p>Sales</p>

    <a href="/manager/daily-sales" class="{{ request()->is('manager/daily-sales') ? 'active' : '' }}">
        📅 Daily Sales
    </a>

    <a href="/manager/sales-report" class="{{ request()->is('manager/sales-report') ? 'active' : '' }}">
        📈 Sales Report
    </a>

    <a href="/manager/collection" class="{{ request()->is('manager/collection') ? 'active' : '' }}">
        🧾 Collection
    </a>

    <p>Inventory</p>

    <a href="/manager/inventory" class="{{ request()->is('manager/inventory') ? 'active' : '' }}">
        📦 Branch Stock
    </a>

    <a href="/manager/inventory-report" class="{{ request()->is('manager/inventory-report') ? 'active' : '' }}">
        📊 Inventory Report
    </a>

    @if(auth()->user()->role != 'audit')
    <a href="/manager/add-stock" class="{{ request()->is('manager/add-stock') ? 'active' : '' }}">
        ➕ Add Stock
    </a>
    @endif

    <a href="/manager/transfer-in" class="{{ request()->is('manager/transfer-in') ? 'active' : '' }}">
        ⬅️ Transfer In
    </a>

    <a href="/manager/transfer-out" class="{{ request()->is('manager/transfer-out') ? 'active' : '' }}">
        ➡️ Transfer Out
    </a>

    <hr>

    <p>Account</p>

    <a href="/manager/change-password" class="{{ request()->is('manager/change-password') ? 'active' : '' }}">
        🔑 Change Password
    </a>

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
                <h1>
                    {{ auth()->user()->role == 'audit' ? 'Audit Workspace' : 'Manager Workspace' }}
                </h1>

                <p>
                    {{ auth()->user()->role == 'audit'
                        ? 'View reports, logs, sales and inventory'
                        : 'Manage approvals, sales, inventory and transfers' }}
                </p>
            </div>

        </div>

        <div class="user-card">

            <div class="avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>

            <div class="user-meta">
                <div class="user-name">{{ auth()->user()->name }}</div>
                <div class="user-role">{{ ucfirst(auth()->user()->role) }}</div>
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