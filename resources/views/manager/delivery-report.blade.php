@extends('layouts.manager')

@section('content')

<style>
.page{
    max-width:1280px;
    margin:auto;
}
.card{
    background:#fff;
    padding:28px;
    border-radius:16px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:end;
    gap:15px;
    flex-wrap:wrap;
    margin-bottom:20px;
}
.title{
    font-size:30px;
    font-weight:900;
    margin-bottom:5px;
}
.sub{
    font-size:13px;
    color:#64748b;
}
.actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}
.filter,
.btn{
    height:42px;
    border-radius:10px;
    border:1px solid #dbeafe;
    padding:0 14px;
    font-size:14px;
}
.filter{
    min-width:220px;
    background:#fff;
}
.btn{
    background:#2563eb;
    color:#fff;
    text-decoration:none;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:700;
}
.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
    margin-bottom:22px;
}
.box{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:14px;
    padding:16px;
}
.box small{
    display:block;
    color:#64748b;
    margin-bottom:6px;
}
.box strong{
    font-size:32px;
}
.table-wrap{
    overflow:auto;
    border:1px solid #e5e7eb;
    border-radius:14px;
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:13px 12px;
    border-bottom:1px solid #eef2f7;
    font-size:14px;
}
th{
    background:#f8fafc;
    color:#475569;
    font-size:13px;
    font-weight:700;
}
.center{text-align:center;}
.right{text-align:right;}
.badge{
    background:#dcfce7;
    color:#166534;
    padding:5px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
}
.empty{
    text-align:center;
    padding:25px;
    color:#64748b;
}
.pagination-wrap{
    margin-top:18px;
    display:flex;
    justify-content:flex-end;
}
.pagination-wrap nav{
    display:flex;
}
.pagination-wrap svg{
    width:18px;
    height:18px;
}
</style>

<div class="page">

    <div class="card">

        <div class="topbar">

            <div>
                <div class="title">🚚 Delivery Report</div>
                <div class="sub">
                    Monitoring of branch deliveries with D.R Number and received stocks
                </div>
            </div>

            <form method="GET" class="actions">

                <select name="branch_id" class="filter" onchange="this.form.submit()">
                    <option value="">All Branches ({{ $totalBranches }})</option>

                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ $branchId == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>

                <a href="{{ route('manager.delivery.report.excel', ['branch_id' => $branchId]) }}"
                   class="btn">
                    📊 Export Excel
                </a>

            </form>

        </div>

        <div class="stats">

            <div class="box">
                <small>Total Deliveries</small>
                <strong>{{ $totalDeliveries }}</strong>
            </div>

            <div class="box">
                <small>Total Qty Delivered</small>
                <strong>{{ number_format($totalQty) }}</strong>
            </div>

            <div class="box">
                <small>Total Value</small>
                <strong>₱{{ number_format($totalValue,2) }}</strong>
            </div>

        </div>

        <div class="table-wrap">
            <table>
                <tr>
                    <th>Date</th>
                    <th>Branch</th>
                    <th>Product</th>
                    <th class="center">Qty</th>
                    <th class="right">Price</th>
                    <th class="right">Total</th>
                    <th>D.R Number</th>
                    <th class="center">Status</th>
                </tr>

                @forelse($rows as $row)
                <tr>
                    <td>{{ $row->created_at->format('Y-m-d') }}</td>
                    <td>{{ $row->branch->name ?? '-' }}</td>
                    <td>{{ $row->product->name ?? '-' }}</td>
                    <td class="center">{{ number_format($row->quantity) }}</td>
                    <td class="right">₱{{ number_format($row->unit_price,2) }}</td>
                    <td class="right">
                        ₱{{ number_format($row->quantity * $row->unit_price,2) }}
                    </td>
                    <td>{{ $row->dr_number }}</td>
                    <td class="center">
                        <span class="badge">Delivered</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="empty">
                        No delivery records found.
                    </td>
                </tr>
                @endforelse

            </table>
        </div>

        @if($rows->hasPages())
            <div class="pagination-wrap">
                {{ $rows->links() }}
            </div>
        @endif

    </div>

</div>

@endsection