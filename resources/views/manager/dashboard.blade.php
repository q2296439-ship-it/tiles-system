@extends('layouts.manager')

@section('content')

<style>
    .header h2 {
        margin: 0;
        font-size: 24px;
        font-weight: bold;
    }

    .header p {
        color: #6b7280;
        margin-bottom: 20px;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .label {
        font-size: 13px;
        color: #6b7280;
        margin-bottom: 5px;
    }

    .stat {
        font-size: 26px;
        font-weight: bold;
    }

    .section-title {
        font-weight: bold;
        margin-bottom: 15px;
        font-size: 16px;
    }

    .flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .badge {
        background: #fee2e2;
        color: #b91c1c;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 11px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table th {
        background: #f9fafb;
        font-size: 13px;
        color: #6b7280;
        text-align: left;
    }

    table th, table td {
        padding: 14px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    /* ALIGNMENT FIX */
    th:nth-child(3),
    td:nth-child(3) {
        text-align: center;
    }

    th:nth-child(4),
    td:nth-child(4) {
        text-align: center;
    }

    /* BUTTON ALIGN FIX */
    td:last-child {
        display: flex;
        justify-content: center;
        gap: 8px;
    }

    .approve {
        background: #22c55e;
        color: white;
        padding: 6px 12px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
    }

    .reject {
        background: #ef4444;
        color: white;
        padding: 6px 12px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
    }

    .danger {
        color: red;
        font-weight: bold;
    }
</style>

<div class="header">
    <h2>📊 Manager Dashboard</h2>
    <p>Welcome, {{ auth()->user()->name }}</p>
</div>

{{-- KPI --}}
<div class="grid">
    <div class="card">
        <div class="label">Today's Sales</div>
        <div class="stat">₱{{ number_format($todaySales ?? 0, 2) }}</div>
    </div>

    <div class="card">
        <div class="label">Monthly Sales</div>
        <div class="stat">₱{{ number_format($monthlySales ?? 0, 2) }}</div>
    </div>

    <div class="card">
        <div class="label">Total Orders</div>
        <div class="stat">{{ $totalOrders ?? 0 }}</div>
    </div>

    <div class="card">
        <div class="label">Low Stock Items</div>
        <div class="stat danger">{{ $lowStockCount ?? 0 }}</div>
    </div>
</div>

{{-- CHART --}}
<div class="card" style="margin-bottom:20px;">
    <div class="section-title">📈 Sales Overview</div>
    <canvas id="salesChart" height="100"></canvas>
</div>

{{-- LOW STOCK --}}
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

{{-- APPROVALS --}}
<div class="card" style="margin-top:20px;">
    <div class="flex">
        <div class="section-title">🧾 Pending Approvals</div>
        <span class="badge">4 Requests</span>
    </div>

    <table>
        <tr>
            <th>Product</th>
            <th>Branch</th>
            <th>Qty</th>
            <th>Action</th>
        </tr>

        <tr>
            <td>Floor Tiles</td>
            <td>Arayat Pampanga</td>
            <td>100</td>
            <td>
                <button class="approve">Approve</button>
                <button class="reject">Reject</button>
            </td>
        </tr>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
new Chart(document.getElementById('salesChart'), {
    type: 'line',
    data: {
        labels: ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'],
        datasets: [{
            label: 'Sales',
            data: [1200,1900,1500,2000,1700,2500,2200],
            borderWidth: 2,
            fill: true
        }]
    },
    options: {
        responsive: true
    }
});
</script>

@endsection