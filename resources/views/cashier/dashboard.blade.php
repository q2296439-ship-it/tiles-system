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
        font-size:28px;
        font-weight:700;
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
        background:linear-gradient(135deg,#ffffff,#f8fafc);
        border-radius:18px;
        padding:20px;
        box-shadow:0 8px 20px rgba(0,0,0,0.05);
        border:1px solid #eef2f7;
    }

    .card-label{
        font-size:13px;
        color:#64748b;
        margin-bottom:8px;
    }

    .card-value{
        font-size:28px;
        font-weight:700;
        color:#0f172a;
    }

    .actions{
        display:grid;
        grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
        gap:18px;
        margin-bottom:20px;
    }

    .action-btn{
        display:block;
        text-decoration:none;
        background:#22c55e;
        color:#fff;
        padding:16px;
        border-radius:16px;
        font-weight:700;
        text-align:center;
        transition:.2s;
    }

    .action-btn:hover{
        transform:translateY(-3px);
        background:#16a34a;
    }

    .grid-2{
        display:grid;
        grid-template-columns:2fr 1fr;
        gap:18px;
    }

    .panel{
        background:#fff;
        border-radius:18px;
        padding:20px;
        box-shadow:0 8px 20px rgba(0,0,0,0.05);
        border:1px solid #eef2f7;
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
        .grid-2{
            grid-template-columns:1fr;
        }
    }
</style>

<div class="topbar">
    <div>
        <div class="title">📊 Cashier Main Dashboard</div>
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
        <div class="card-value">₱{{ number_format($todaySales,2) }}</div>
    </div>

    <div class="card-box">
        <div class="card-label">Receipts Today</div>
        <div class="card-value">{{ $receiptCount }}</div>
    </div>

    <div class="card-box">
        <div class="card-label">Products</div>
        <div class="card-value">{{ $products->count() }}</div>
    </div>

    <div class="card-box">
        <div class="card-label">Low Stocks</div>
        <div class="card-value">{{ $lowStocks->count() }}</div>
    </div>

</div>

<div class="actions">

    <a href="{{ route('cashier.collection.create') }}" class="action-btn">
        ➕ Add Collection Receipt
    </a>

    <a href="{{ route('cashier.collection.today') }}" class="action-btn">
        🧾 Collection Today
    </a>

    <a href="{{ route('cashier.deposit') }}" class="action-btn">
        💰 Deposit
    </a>

    <a href="{{ route('cashier.return.create') }}" class="action-btn">
        ↩ Return Receipt
    </a>

</div>

<div class="grid-2">

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
        <h3>⚠ Low Stock Alerts</h3>

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
                        <span class="badge-low">
                            {{ $item->stock }}
                        </span>
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

</div>

@endsection

@section('cart')
@endsection

@section('scripts')
@endsection