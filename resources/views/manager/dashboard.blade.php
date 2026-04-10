@extends('layouts.manager')

@section('content')

<style>
    .header {
        margin-bottom: 20px;
    }

    .header h2 {
        font-size: 22px;
        font-weight: bold;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .stat {
        font-size: 22px;
        font-weight: bold;
    }

    .label {
        font-size: 13px;
        color: gray;
    }

    .section-title {
        font-weight: bold;
        margin-bottom: 10px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table th {
        background: #f9fafb;
        font-size: 13px;
        color: #6b7280;
    }

    table th, table td {
        padding: 10px;
        border-bottom: 1px solid #e5e7eb;
    }

    .approve {
        background: #22c55e;
        color: white;
        border-radius: 5px;
        padding: 5px 10px;
        border: none;
    }

    .reject {
        background: #ef4444;
        color: white;
        border-radius: 5px;
        padding: 5px 10px;
        border: none;
    }

    .danger {
        color: red;
        font-weight: bold;
    }

    .badge {
        background: #fee2e2;
        color: #b91c1c;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 11px;
    }

    .flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
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
        <div class="label">Low Stock</div>
        <div class="stat danger">{{ $lowStockCount ?? 0 }}</div>
    </div>
</div>

{{-- CHART + TOP PRODUCTS --}}
<div class="grid">

    <div class="card" style="grid-column: span 2;">
        <div class="flex">
            <div class="section-title">📈 Sales Overview</div>
        </div>

        <canvas id="salesChart" height="100"></canvas>
    </div>

    <div class="card">
        <div class="section-title">🔥 Top Products</div>

        <ul>
            @if(isset($topProducts))
                @foreach($topProducts as $item)
                    <li>{{ $item->product_name }} - {{ $item->total_qty }}</li>
                @endforeach
            @else
                <li>Sample Product A - 120</li>
                <li>Sample Product B - 95</li>
            @endif
        </ul>
    </div>

</div>

{{-- LOW STOCK --}}
<div class="card">
    <div class="section-title">⚠️ Low Stock Alert</div>

    <table>
        <tr>
            <th>Product</th>
            <th>Stock</th>
        </tr>

        @if(isset($lowStocks))
            @foreach($lowStocks as $p)
            <tr>
                <td>{{ $p->name }}</td>
                <td class="danger">{{ $p->stock }}</td>
            </tr>
            @endforeach
        @else
            <tr>
                <td>Sample Product</td>
                <td class="danger">5</td>
            </tr>
        @endif
    </table>
</div>

{{-- APPROVALS --}}
<div class="card">
    <div class="flex">
        <div class="section-title">🧾 Pending Approvals</div>
        <span class="badge">{{ isset($requests) ? count($requests) : 3 }} Requests</span>
    </div>

    <table>
        <tr>
            <th>Product</th>
            <th>Branch</th>
            <th>Qty</th>
            <th>Action</th>
        </tr>

        @if(isset($requests))
            @foreach($requests as $req)
            <tr>
                <td>{{ optional($req->product)->name }}</td>
                <td>{{ optional($req->branch)->name }}</td>
                <td>{{ $req->quantity }}</td>
                <td>
                    <form method="POST" action="/admin/manager/approve/{{ $req->id }}" style="display:inline;">
                        @csrf
                        <button class="approve">Approve</button>
                    </form>
                    <form method="POST" action="/admin/manager/reject/{{ $req->id }}" style="display:inline;">
                        @csrf
                        <button class="reject">Reject</button>
                    </form>
                </td>
            </tr>
            @endforeach
        @else
            <tr>
                <td>Sample</td>
                <td>Main</td>
                <td>10</td>
                <td>
                    <button class="approve">Approve</button>
                    <button class="reject">Reject</button>
                </td>
            </tr>
        @endif
    </table>
</div>

{{-- CHART SCRIPT --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const ctx = document.getElementById('salesChart');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Sales',
                data: [1200, 1900, 1500, 2000, 1700, 2500, 2200],
                borderWidth: 2,
                fill: true
            }]
        },
        options: {
            responsive: true,
        }
    });
</script>

@endsection