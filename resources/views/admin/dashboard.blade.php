@extends('layouts.admin')

@section('content')

<style>
.dashboard-wrap{
    max-width:1280px;
    margin:auto;
    font-family:'Segoe UI',Tahoma,sans-serif;
}

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:20px;
    flex-wrap:wrap;
    margin-bottom:25px;
}

.page-title{
    font-size:34px;
    font-weight:700;
    color:#111827;
    margin:0;
}

.page-sub{
    color:#6b7280;
    font-size:15px;
    margin-top:6px;
}

.top-actions{
    display:flex;
    align-items:center;
    gap:12px;
    flex-wrap:wrap;
}

.filter-form select{
    padding:11px 14px;
    border:1px solid #d1d5db;
    border-radius:10px;
    font-size:14px;
    outline:none;
    min-width:220px;
    background:#fff;
}

.user-chip{
    background:#fff;
    padding:10px 14px;
    border-radius:10px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
    font-weight:600;
}

.stats-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:18px;
    margin-bottom:22px;
}

.stat-card{
    border-radius:18px;
    padding:22px;
    color:#fff;
    box-shadow:0 10px 24px rgba(0,0,0,0.08);
}

.stat-card small{
    display:block;
    opacity:.9;
    font-size:14px;
    margin-bottom:12px;
}

.stat-card h2{
    margin:0;
    font-size:34px;
    font-weight:800;
}

.blue{ background:linear-gradient(135deg,#3b82f6,#2563eb); }
.green{ background:linear-gradient(135deg,#22c55e,#16a34a); }
.orange{ background:linear-gradient(135deg,#f59e0b,#d97706); }
.red{ background:linear-gradient(135deg,#ef4444,#dc2626); }

.main-grid{
    display:grid;
    grid-template-columns:2fr 1fr;
    gap:20px;
    margin-bottom:20px;
}

.card{
    background:#fff;
    border-radius:18px;
    padding:22px;
    box-shadow:0 8px 24px rgba(0,0,0,0.05);
}

.card-title{
    font-size:22px;
    font-weight:700;
    color:#111827;
    margin-bottom:16px;
}

.table-wrap{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    text-align:left;
    background:#f9fafb;
    color:#374151;
    font-size:13px;
    font-weight:700;
}

th,td{
    padding:13px;
    border-bottom:1px solid #e5e7eb;
}

tr:hover{
    background:#f9fafb;
}

.low-badge{
    background:#fee2e2;
    color:#dc2626;
    padding:5px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
}

.amount{
    font-weight:700;
    color:#16a34a;
}

.empty{
    text-align:center;
    color:#6b7280;
    padding:18px;
}

@media (max-width:1100px){
    .stats-grid{
        grid-template-columns:repeat(2,1fr);
    }

    .main-grid{
        grid-template-columns:1fr;
    }
}

@media (max-width:700px){
    .stats-grid{
        grid-template-columns:1fr;
    }

    .page-title{
        font-size:28px;
    }
}
</style>

<div class="dashboard-wrap">

    <!-- HEADER -->
    <div class="topbar">

        <div>
            <h1 class="page-title">📊 Admin Dashboard</h1>
            <div class="page-sub">
                Welcome back, monitor your branches and sales performance.
            </div>
        </div>

        <div class="top-actions">

            <form method="GET" class="filter-form">
                <select name="branch_id" onchange="this.form.submit()">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <div class="user-chip">👤 Admin</div>

        </div>

    </div>

    <!-- KPI -->
    <div class="stats-grid">

        <div class="stat-card blue">
            <small>Total Products</small>
            <h2>{{ $totalProducts }}</h2>
        </div>

        <div class="stat-card green">
            <small>Today Sales</small>
            <h2>₱{{ number_format($todaySales, 2) }}</h2>
        </div>

        <div class="stat-card orange">
            <small>Transactions</small>
            <h2>{{ $transactionsToday }}</h2>
        </div>

        <div class="stat-card red">
            <small>Low Stocks</small>
            <h2>{{ $lowStocks->count() }}</h2>
        </div>

    </div>

    <!-- CHART + LOW STOCK -->
    <div class="main-grid">

        <div class="card">
            <div class="card-title">📈 Sales Overview</div>
            <canvas id="salesChart" height="110"></canvas>
        </div>

        <div class="card">
            <div class="card-title">⚠ Low Stock Items</div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Stock</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($lowStocks->take(6) as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>
                                <span class="low-badge">{{ $item->stock }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="empty">No low stock items</td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <!-- RECENT TRANSACTIONS -->
    <div class="card">
        <div class="card-title">🧾 Recent Transactions</div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Amount</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($recentSales as $sale)
                    <tr>
                        <td>{{ $sale->created_at }}</td>
                        <td class="amount">₱{{ number_format($sale->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="empty">No transactions found</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
const labels = {!! json_encode($salesData->keys()) !!};
const data = {!! json_encode($salesData->values()) !!};

new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: labels,
        datasets: [{
            label: 'Sales',
            data: data,
            fill: true,
            borderWidth: 3,
            tension: .35,
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true
            }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

@endsection