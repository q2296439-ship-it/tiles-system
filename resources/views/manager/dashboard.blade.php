@extends('layouts.manager')

@section('content')

@php
    $isAudit = strtolower(auth()->user()->role) === 'audit';
@endphp

<style>
.dashboard-wrap{
    max-width:1280px;
    margin:auto;
}

.dashboard-header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    flex-wrap:wrap;
    margin-bottom:25px;
}

.title-box h2{
    margin:0;
    font-size:34px;
    font-weight:800;
    color:#0f172a;
}

.title-box p{
    margin-top:6px;
    color:#64748b;
    font-size:14px;
}

.branch-filter form{
    display:flex;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
}

.branch-filter label{
    font-size:14px;
    font-weight:700;
    color:#111827;
}

.branch-filter select{
    padding:10px 14px;
    border:1px solid #d1d5db;
    border-radius:10px;
    min-width:220px;
    background:#fff;
    font-size:14px;
}

.grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin-bottom:22px;
}

.card{
    background:#fff;
    padding:22px;
    border-radius:18px;
    box-shadow:0 8px 22px rgba(0,0,0,.05);
}

.kpi.blue{
    background:linear-gradient(135deg,#3b82f6,#2563eb);
    color:#fff;
}

.kpi.green{
    background:linear-gradient(135deg,#22c55e,#16a34a);
    color:#fff;
}

.kpi.orange{
    background:linear-gradient(135deg,#f59e0b,#d97706);
    color:#fff;
}

.kpi.red{
    background:linear-gradient(135deg,#ef4444,#dc2626);
    color:#fff;
}

.label{
    font-size:13px;
    opacity:.95;
    margin-bottom:10px;
}

.stat{
    font-size:34px;
    font-weight:800;
}

.section-title{
    font-size:22px;
    font-weight:800;
    color:#0f172a;
    margin-bottom:15px;
}

.main-grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
    margin-bottom:20px;
}

.flex{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    margin-bottom:10px;
}

.badge{
    background:#fee2e2;
    color:#b91c1c;
    padding:5px 10px;
    border-radius:999px;
    font-size:11px;
    font-weight:700;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#f9fafb;
    font-size:13px;
    color:#6b7280;
    text-align:left;
    font-weight:700;
}

th,td{
    padding:14px;
    border-bottom:1px solid #e5e7eb;
    vertical-align:middle;
}

.danger{
    color:#dc2626;
    font-weight:700;
}

.empty{
    text-align:center;
    color:#64748b;
    padding:18px;
}

.action-wrap{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
}

.approve,
.reject{
    border:none;
    padding:8px 12px;
    border-radius:8px;
    cursor:pointer;
    font-weight:700;
}

.approve{
    background:#16a34a;
    color:#fff;
}

.reject{
    background:#dc2626;
    color:#fff;
}

@media(max-width:1100px){
    .grid{
        grid-template-columns:repeat(2,1fr);
    }

    .main-grid{
        grid-template-columns:1fr;
    }
}

@media(max-width:700px){
    .grid{
        grid-template-columns:1fr;
    }

    .title-box h2{
        font-size:28px;
    }

    .dashboard-header{
        flex-direction:column;
        align-items:flex-start;
    }

    .branch-filter form{
        width:100%;
    }

    .branch-filter select{
        width:100%;
        min-width:unset;
    }
}
</style>

<div class="dashboard-wrap">

    <!-- HEADER -->
    <div class="dashboard-header">

        <div class="title-box">
            <h2>{{ $isAudit ? '📊 Audit Dashboard' : '📊 Manager Dashboard' }}</h2>

            <p>
                {{ $isAudit
                    ? 'Welcome, '.auth()->user()->name.' • Read Only Access'
                    : 'Welcome, '.auth()->user()->name }}
            </p>
        </div>

        <div class="branch-filter">
            <form method="GET" action="/manager">
                <label>Select Branch:</label>

                <select name="branch_id" onchange="this.form.submit()">
                    <option value="">All Branches</option>

                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ (isset($branchId) && $branchId == $branch->id) ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>

    </div>

    <!-- KPI -->
    <div class="grid">

        <div class="card kpi blue">
            <div class="label">Today's Sales</div>
            <div class="stat">₱{{ number_format($todaySales ?? 0, 2) }}</div>
        </div>

        <div class="card kpi green">
            <div class="label">Monthly Sales</div>
            <div class="stat">₱{{ number_format($monthlySales ?? 0, 2) }}</div>
        </div>

        <div class="card kpi orange">
            <div class="label">Total Orders</div>
            <div class="stat">{{ $totalOrders ?? 0 }}</div>
        </div>

        <div class="card kpi red">
            <div class="label">Low Stock Items</div>
            <div class="stat">{{ $lowStockCount ?? 0 }}</div>
        </div>

    </div>

    <!-- CHART + LOW STOCK -->
    <div class="main-grid">

        <div class="card">
            <div class="section-title">📈 Sales Overview</div>
            <canvas id="salesChart" height="110"></canvas>
        </div>

        <div class="card">
            <div class="section-title">⚠️ Low Stock Alert</div>

            <table>
                <tr>
                    <th>Product</th>
                    <th style="text-align:center;">Stock</th>
                </tr>

                <tr>
                    <td>Sample Product</td>
                    <td class="danger" style="text-align:center;">5</td>
                </tr>
            </table>
        </div>

    </div>

    <!-- APPROVALS -->
    <div class="card">
        <div class="flex">
            <div class="section-title">🧾 Pending Approvals</div>
            <span class="badge">{{ count($requests) }} Requests</span>
        </div>

        <table>
            <tr>
                <th>Product</th>
                <th>Branch</th>
                <th>Qty</th>
                <th>Action</th>
            </tr>

            @forelse($requests as $req)
            <tr>
                <td>{{ $req->product->name }}</td>

                <td>
                    {{ $req->from_branch->name ?? 'N/A' }}
                    →
                    {{ $req->branch->name ?? 'N/A' }}
                </td>

                <td>{{ $req->quantity }}</td>

                <td>
                    @if(!$isAudit)
                    <div class="action-wrap">

                        <form method="POST" action="/admin/manager/approve/{{ $req->id }}">
                            @csrf
                            <button class="approve">Approve</button>
                        </form>

                        <form method="POST" action="/admin/manager/reject/{{ $req->id }}">
                            @csrf
                            <button class="reject">Reject</button>
                        </form>

                    </div>
                    @else
                        <span class="empty">Read Only</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="empty">No pending requests</td>
            </tr>
            @endforelse

        </table>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('salesChart'), {
    type:'line',
    data:{
        labels:['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
        datasets:[{
            label:'Sales',
            data:[1200,1900,1500,2000,1700,2500,2200],
            borderWidth:3,
            fill:true,
            tension:.35,
            pointRadius:4
        }]
    },
    options:{
        responsive:true,
        plugins:{
            legend:{
                display:true
            }
        },
        scales:{
            y:{
                beginAtZero:true
            }
        }
    }
});
</script>

@endsection