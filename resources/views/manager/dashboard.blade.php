@extends('layouts.manager')

@section('content')

<style>
    body {
        background: #f3f4f6;
    }

    .header {
        margin-bottom: 20px;
    }

    .header h2 {
        font-size: 24px;
        font-weight: bold;
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 25px;
    }

    .card {
        background: white;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 20px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        transition: 0.2s;
    }

    .card:hover {
        transform: translateY(-3px);
    }

    .stat {
        font-size: 24px;
        font-weight: bold;
    }

    .label {
        font-size: 13px;
        color: #6b7280;
    }

    .section-title {
        font-weight: bold;
        margin-bottom: 10px;
        font-size: 16px;
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
        border-bottom: 1px solid #e5e7eb;
        padding: 10px;
        text-align: left;
    }

    button {
        padding: 5px 10px;
        border: none;
        cursor: pointer;
        border-radius: 6px;
        font-size: 12px;
    }

    .approve {
        background: #22c55e;
        color: white;
    }

    .reject {
        background: #ef4444;
        color: white;
    }

    .danger {
        color: #ef4444;
        font-weight: bold;
    }

    .flex {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .badge {
        background: #fee2e2;
        color: #b91c1c;
        padding: 3px 8px;
        border-radius: 999px;
        font-size: 11px;
    }
</style>

{{-- HEADER --}}
<div class="header">
    <h2>📊 Manager Dashboard</h2>
    <p>Welcome, {{ auth()->user()->name }}</p>
</div>

{{-- KPI CARDS --}}
<div class="grid">

    <div class="card">
        <div class="label">💰 Today's Sales</div>
        <div class="stat">₱{{ number_format($todaySales ?? 0, 2) }}</div>
    </div>

    <div class="card">
        <div class="label">📈 Monthly Sales</div>
        <div class="stat">₱{{ number_format($monthlySales ?? 0, 2) }}</div>
    </div>

    <div class="card">
        <div class="label">🧾 Total Orders</div>
        <div class="stat">{{ $totalOrders ?? 0 }}</div>
    </div>

    <div class="card">
        <div class="label">⚠️ Low Stock Items</div>
        <div class="stat danger">{{ $lowStockCount ?? 0 }}</div>
    </div>

</div>

{{-- MAIN GRID --}}
<div class="grid">

    {{-- SALES OVERVIEW --}}
    <div class="card" style="grid-column: span 2;">
        <div class="flex">
            <div class="section-title">📈 Sales Overview</div>
            <select>
                <option>Today</option>
                <option>This Week</option>
                <option>This Month</option>
            </select>
        </div>

        <div style="height:180px; display:flex; align-items:center; justify-content:center; color:gray;">
            (Chart coming soon)
        </div>
    </div>

    {{-- TOP PRODUCTS --}}
    <div class="card">
        <div class="section-title">🔥 Top Products</div>

        <ul style="padding-left: 15px; font-size:14px;">
            @if(isset($topProducts))
                @foreach($topProducts as $item)
                    <li>{{ $item->product_name }} - {{ $item->total_qty }} sold</li>
                @endforeach
            @else
                <li>Sample Product A - 120 sold</li>
                <li>Sample Product B - 95 sold</li>
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
            @forelse($lowStocks as $product)
            <tr>
                <td>{{ $product->name }}</td>
                <td class="danger">{{ $product->stock }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="2">No low stock items</td>
            </tr>
            @endforelse
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
        <span class="badge">
            {{ isset($requests) ? count($requests) : 1 }} Requests
        </span>
    </div>

    <table>
        <tr>
            <th>Product</th>
            <th>Branch</th>
            <th>Qty</th>
            <th>Action</th>
        </tr>

        @if(isset($requests))
            @forelse($requests as $req)
            <tr>
                <td>{{ optional($req->product)->name ?? 'N/A' }}</td>
                <td>{{ optional($req->branch)->name ?? 'N/A' }}</td>
                <td>{{ $req->quantity }}</td>

                <td>
                    <form method="POST" action="/admin/manager/approve/{{ $req->id }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="approve">Approve</button>
                    </form>

                    <form method="POST" action="/admin/manager/reject/{{ $req->id }}" style="display:inline;">
                        @csrf
                        <button type="submit" class="reject">Reject</button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4">No pending requests</td>
            </tr>
            @endforelse
        @else
            <tr>
                <td>Sample Product</td>
                <td>Main Branch</td>
                <td>10</td>
                <td>
                    <button class="approve">Approve</button>
                    <button class="reject">Reject</button>
                </td>
            </tr>
        @endif
    </table>
</div>

@endsection