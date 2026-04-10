@extends('layouts.manager')

@section('content')

<style>
    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 20px;
        margin-bottom: 20px;
    }

    .card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.05);
    }

    .stat {
        font-size: 22px;
        font-weight: bold;
    }

    .label {
        font-size: 14px;
        color: gray;
    }

    table {
        width: 100%;
        border-collapse: collapse;
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
        border-radius: 5px;
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
</style>

<div class="card">
    <h2>📊 Manager Dashboard</h2>
    <p>Welcome, {{ auth()->user()->name }}</p>
</div>

{{-- 🔥 KPI CARDS --}}
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

{{-- 📈 TOP SELLING PRODUCTS --}}
<div class="card">
    <h3>🔥 Top Selling Products</h3>

    <table>
        <tr>
            <th>Product</th>
            <th>Sold Qty</th>
        </tr>

        @forelse($topProducts as $item)
        <tr>
            <td>{{ $item->product_name }}</td>
            <td>{{ $item->total_qty }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="2">No data available</td>
        </tr>
        @endforelse
    </table>
</div>

{{-- ⚠️ LOW STOCK ALERT --}}
<div class="card">
    <h3>⚠️ Low Stock Alert</h3>

    <table>
        <tr>
            <th>Product</th>
            <th>Stock</th>
        </tr>

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
    </table>
</div>

{{-- 🧾 PENDING APPROVALS --}}
<div class="card">
    <h3>🧾 Pending Approvals</h3>

    <table>
        <tr>
            <th>Product</th>
            <th>Branch</th>
            <th>Qty</th>
            <th>Action</th>
        </tr>

        @foreach($requests as $req)
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
        @endforeach
    </table>
</div>

@endsection