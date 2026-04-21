<!DOCTYPE html>
<html>
<head>
    <title>Cashier Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        *{box-sizing:border-box;margin:0;padding:0;}
        html,body{width:100%;overflow-x:hidden;}
        body{
            font-family:'Segoe UI',sans-serif;
            background:#f1f5f9;
            color:#111827;
        }
        a{text-decoration:none;}

        .sidebar{
            width:240px;
            height:100vh;
            background:linear-gradient(180deg,#0f172a,#111827,#020617);
            color:#fff;
            position:fixed;
            top:0;
            left:0;
            display:flex;
            flex-direction:column;
            z-index:1000;
            transition:.3s ease;
            overflow:hidden;
        }

        .sidebar.show{left:0;}
        .sidebar.hide{left:-250px;}

        .sidebar-menu{
            flex:1;
            overflow-y:auto;
            padding:18px 16px;
        }

        .sidebar h2{
            font-size:16px;
            font-weight:800;
            margin-bottom:20px;
            white-space:nowrap;
            color:#fff;
        }

        .sidebar p{
            font-size:11px;
            color:#94a3b8;
            margin:18px 0 8px;
            text-transform:uppercase;
            letter-spacing:1px;
        }

        .sidebar a{
            display:block;
            padding:12px 14px;
            border-radius:12px;
            color:#e5e7eb;
            font-size:14px;
            margin-bottom:8px;
            transition:.2s;
            white-space:nowrap;
        }

        .sidebar a:hover{
            background:rgba(59,130,246,.12);
            color:#fff;
        }

        .sidebar a.active{
            background:linear-gradient(90deg,#2563eb,#3b82f6);
            color:#fff;
            font-weight:700;
        }

        hr{
            border:none;
            border-top:1px solid rgba(148,163,184,.15);
            margin:12px 0;
        }

        .logout{padding:14px 16px;}

        .logout button{
            width:100%;
            border:none;
            padding:12px;
            border-radius:12px;
            background:#ef4444;
            color:#fff;
            font-weight:700;
            cursor:pointer;
        }

        .logout button:hover{background:#dc2626;}

        .main{
            margin-left:240px;
            width:calc(100% - 240px);
            min-height:100vh;
            transition:.3s;
        }

        .topbar{
            background:#fff;
            padding:15px 18px;
            border-bottom:1px solid #e5e7eb;
            display:flex;
            justify-content:space-between;
            align-items:center;
            gap:15px;
            position:sticky;
            top:0;
            z-index:5000;
            width:100%;
            overflow:visible;
        }

        .left-wrap{
            display:flex;
            align-items:center;
            gap:12px;
            min-width:0;
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
            flex-shrink:0;
        }

        .topbar-left h1{
            font-size:18px;
            font-weight:800;
            line-height:1.2;
            margin:0;
        }

        .topbar-left p{
            font-size:12px;
            color:#6b7280;
            margin-top:2px;
            margin-bottom:0;
        }

        .right-wrap{
            display:flex;
            align-items:center;
            gap:10px;
        }

        .bell-wrap{
            position:relative;
            z-index:6000;
        }

        .bell-box{
            position:relative;
            width:42px;
            height:42px;
            border-radius:12px;
            background:#f8fafc;
            display:flex;
            align-items:center;
            justify-content:center;
            font-size:20px;
            cursor:pointer;
            border:1px solid #e5e7eb;
        }

        .bell-count{
            position:absolute;
            top:-4px;
            right:-4px;
            background:#ef4444;
            color:#fff;
            font-size:11px;
            min-width:18px;
            height:18px;
            padding:0 5px;
            border-radius:999px;
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight:700;
        }

        .bell-dropdown{
            display:none;
            position:fixed;
            top:78px;
            right:130px;
            width:340px;
            max-width:90vw;
            background:#fff;
            border:1px solid #e5e7eb;
            border-radius:12px;
            box-shadow:0 14px 35px rgba(0,0,0,.18);
            z-index:999999;
            overflow:hidden;
        }

        .bell-item{
            padding:12px;
            border-bottom:1px solid #f1f5f9;
            font-size:13px;
            line-height:1.4;
        }

        .bell-item small{color:#64748b;}

        .bell-empty{
            padding:15px;
            text-align:center;
            color:#64748b;
        }

        .bell-view{
            display:block;
            text-align:center;
            padding:10px;
            background:#eff6ff;
            color:#2563eb;
            font-weight:700;
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
            width:36px;
            height:36px;
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
            white-space:nowrap;
        }

        .user-info span{
            font-size:11px;
            color:#6b7280;
        }

        .content{
            padding:24px;
            width:100%;
            max-width:100%;
        }

        .content > *{
            width:100%;
            max-width:100%;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        .table-responsive{
            width:100%;
            overflow-x:auto;
        }

        .overlay{
            position:fixed;
            inset:0;
            background:rgba(0,0,0,.4);
            display:none;
            z-index:999;
        }

        .overlay.show{display:block;}

        @media(max-width:768px){
            .menu-btn{
                display:flex;
                align-items:center;
                justify-content:center;
            }

            .sidebar{left:-250px;}
            .sidebar.show{left:0;}

            .main{
                margin-left:0;
                width:100%;
            }

            .topbar-left p,
            .user-info{
                display:none;
            }

            .content{padding:14px;}

            table{min-width:700px;}

            .bell-dropdown{
                top:70px;
                right:15px;
                width:300px;
            }
        }

        @media(max-width:480px){
            .content{padding:12px;}

            .sidebar a{font-size:13px;}

            .avatar{
                width:32px;
                height:32px;
            }

            .bell-dropdown{
                width:260px;
                right:10px;
            }
        }
    </style>
</head>

<body>

@php
$notes = \App\Models\Announcement::where('is_active',1)
    ->latest()
    ->take(5)
    ->get();

$readIds = \DB::table('announcement_reads')
    ->where('user_id', auth()->id())
    ->pluck('announcement_id')
    ->toArray();

$unreadCount = $notes->whereNotIn('id', $readIds)->count();
@endphp

<div class="overlay" id="overlay" onclick="closeSidebar()"></div>

<div class="sidebar" id="sidebar">

    <div class="sidebar-menu">

        <h2>💰 Cashier Panel</h2>

        <p>Main</p>
        <a href="{{ url('/cashier') }}" class="{{ request()->is('cashier') ? 'active' : '' }}">📊 Dashboard</a>
        <a href="/announcements" class="{{ request()->is('announcements') ? 'active' : '' }}">📢 Announcements</a>

        <p>Sales</p>

        <a href="{{ route('cashier.collection.create') }}" class="{{ request()->is('cashier/collection-receipt*') ? 'active' : '' }}">🧾 Add Collection Receipt</a>
        <a href="{{ route('cashier.collection.cancel') }}" class="{{ request()->is('cashier/collection-cancel*') ? 'active' : '' }}">❌ Cancel Receipt</a>
        <a href="{{ route('cashier.return.create') }}" class="{{ request()->is('cashier/return-receipt*') ? 'active' : '' }}">↩ Return Receipt</a>
        <a href="{{ route('cashier.delivery.fee') }}" class="{{ request()->is('cashier/delivery-fee*') ? 'active' : '' }}">🚚 Delivery Fee</a>
        <a href="{{ url('/cashier/ar-accounts') }}" class="{{ request()->is('cashier/ar-accounts*') ? 'active' : '' }}">📒 A/R Accounts</a>
        <a href="{{ url('/cashier/collection-today') }}" class="{{ request()->is('cashier/collection-today*') ? 'active' : '' }}">📊 Collection Today</a>
        <a href="{{ url('/cashier/deposit') }}" class="{{ request()->is('cashier/deposit*') ? 'active' : '' }}">🏦 Deposit</a>

        <p>Cash Flow</p>

        <a href="{{ route('cashier.total.cash') }}" class="{{ request()->is('cashier/cash-total*') ? 'active' : '' }}">💰 Total Cash</a>
        <a href="{{ route('cashier.transfer.cash') }}" class="{{ request()->is('cashier/cash-transfer*') ? 'active' : '' }}">🔄 B2B Cash Transfer</a>
        <a href="{{ route('cashier.expenses') }}" class="{{ request()->is('cashier/expenses*') ? 'active' : '' }}">🧾 Store Expenses</a>

        <p>Inventory</p>

        <a href="{{ route('cashier.inventory.stock') }}" class="{{ request()->is('cashier/inventory-stock') ? 'active' : '' }}">📦 Inventory Stock</a>
        <a href="/cashier/add-stock" class="{{ request()->is('cashier/add-stock*') ? 'active' : '' }}">➕ Add New Stock</a>
        <a href="{{ route('cashier.transfer.in') }}" class="{{ request()->is('cashier/transfer-in*') ? 'active' : '' }}">⬇ Transfer In</a>
        <a href="{{ url('/cashier/incoming') }}" class="{{ request()->is('cashier/incoming*') ? 'active' : '' }}">📦 Incoming Transfer Stock</a>

        <hr>

        <p>Account</p>
        <a href="{{ route('cashier.password') }}" class="{{ request()->is('cashier/change-password*') ? 'active' : '' }}">🔑 Change Password</a>

    </div>

    <div class="logout">
        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button type="submit">🚪 Logout</button>
        </form>
    </div>

</div>

<div class="main">

    <div class="topbar">

        <div class="left-wrap">
            <button class="menu-btn" onclick="toggleSidebar()">☰</button>

            <div class="topbar-left">
                <h1>Cashier Workspace</h1>
                <p>Manage collections, sales, inventory and transfers</p>
            </div>
        </div>

        <div class="right-wrap">

            <div class="bell-wrap">
                <div class="bell-box" onclick="toggleBell(event)">
                    🔔
                    @if($unreadCount > 0)
                        <span class="bell-count" id="bellCount">{{ $unreadCount }}</span>
                    @endif
                </div>

                <div class="bell-dropdown" id="bellDropdown">
                    @forelse($notes as $note)
                        <div class="bell-item">
                            <strong>{{ $note->title }}</strong><br>
                            <small>{{ $note->message }}</small>
                        </div>
                    @empty
                        <div class="bell-empty">No announcements</div>
                    @endforelse

                    <a href="/announcements?view=1" class="bell-view" onclick="clearBellCount()">View All</a>
                </div>
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

function clearBellCount(){
    let badge = document.getElementById('bellCount');

    if(badge){
        badge.remove();
    }

    fetch('/announcements/read', {
        method:'POST',
        headers:{
            'X-CSRF-TOKEN':'{{ csrf_token() }}',
            'Content-Type':'application/json'
        }
    });
}

function toggleBell(event){
    event.stopPropagation();

    let box = document.getElementById('bellDropdown');

    if(box.style.display === 'block'){
        box.style.display = 'none';
    }else{
        box.style.display = 'block';
    }
}

document.addEventListener('click', function(e){
    let wrap = document.querySelector('.bell-wrap');
    let box = document.getElementById('bellDropdown');

    if(!wrap.contains(e.target)){
        box.style.display = 'none';
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

@yield('scripts')

</body>
</html>