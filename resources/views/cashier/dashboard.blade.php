@extends('layouts.cashier')

@section('content')

<style>
    .topbar{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:20px;
        gap:15px;
        flex-wrap:wrap;
    }

    .title{
        font-size:30px;
        font-weight:800;
        color:#0f172a;
    }

    .subtitle{
        color:#64748b;
        font-size:14px;
        margin-top:4px;
    }

    .cards{
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
        gap:18px;
        margin-bottom:20px;
    }

    .card-box{
        background:#ffffff;
        border-radius:18px;
        padding:22px;
        box-shadow:0 8px 20px rgba(0,0,0,0.05);
        border:1px solid #eef2f7;
    }

    .card-label{
        font-size:13px;
        color:#64748b;
        margin-bottom:10px;
    }

    .card-value{
        font-size:30px;
        font-weight:800;
        color:#0f172a;
    }

    .green{ color:#16a34a; }
    .blue{ color:#2563eb; }
    .orange{ color:#ea580c; }
    .red{ color:#dc2626; }

    .panel{
        background:#fff;
        border-radius:18px;
        padding:20px;
        box-shadow:0 8px 20px rgba(0,0,0,0.05);
        border:1px solid #eef2f7;
        margin-bottom:20px;
    }

    .panel h3{
        margin:0 0 15px;
        font-size:18px;
        color:#0f172a;
    }

    table{
        width:100%;
        border-collapse:collapse;
    }

    th,td{
        padding:12px 10px;
        border-bottom:1px solid #f1f5f9;
        text-align:left;
        font-size:14px;
    }

    th{
        color:#64748b;
        font-weight:600;
    }

    .badge-low{
        background:#fee2e2;
        color:#b91c1c;
        padding:4px 8px;
        border-radius:999px;
        font-size:12px;
        font-weight:700;
    }

    .empty{
        color:#94a3b8;
        font-size:14px;
    }

    @media(max-width:900px){
        .cards{
            grid-template-columns:1fr;
        }
    }
</style>

<div class="topbar">
    <div>
        <div class="title">📊 Cashier Dashboard</div>
        <div class="subtitle">
            Welcome back, {{ auth()->user()->username }}
        </div>
    </div>

    <div class="subtitle">
        {{ now()->format('F d, Y h:i A') }}
    </div>
</div>

<div class="cards">

    <div class="card-box">
        <div class="card-label">Today Sales</div>
        <div class="card-value green">₱{{ number_format($todaySales,2) }}</div>
    </div>

    <div class="card-box">
        <div class="card-label">Receipts Today</div>
        <div class="card-value blue">{{ $receiptCount }}</div>
    </div>

    <div class="card-box">
        <div class="card-label">Available Products</div>
        <div class="card-value orange">{{ $products->count() }}</div>
    </div>

    <div class="card-box">
        <div class="card-label">Low Stock Alerts</div>
        <div class="card-value red">{{ $lowStocks->count() }}</div>
    </div>

</div>

<div class="panel">
    <h3>🕒 Recent Sales</h3>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Total</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentSales as $sale)
            <tr>
                <td>#{{ $sale->id }}</td>
                <td>₱{{ number_format($sale->total_amount,2) }}</td>
                <td>{{ $sale->created_at->format('M d, h:i A') }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="3" class="empty">No recent sales found.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="panel">
    <h3>⚠ Low Stock Items</h3>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Stock</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lowStocks as $item)
            <tr>
                <td>{{ $item->name }}</td>
                <td>
                    <span class="badge-low">{{ $item->stock }}</span>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="2" class="empty">No low stock items.</td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection

@section('cart')
<div></div>
@endsection

@section('scripts')
@endsection