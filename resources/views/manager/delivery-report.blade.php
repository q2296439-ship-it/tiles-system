@extends('layouts.manager')

@section('content')

<style>
.page{
    max-width:1200px;
    margin:auto;
}
.card{
    background:#fff;
    padding:25px;
    border-radius:14px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}
.title{
    font-size:28px;
    font-weight:900;
    margin-bottom:6px;
}
.sub{
    font-size:13px;
    color:#64748b;
    margin-bottom:20px;
}
.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
    margin-bottom:20px;
}
.box{
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:12px;
    padding:15px;
}
.box small{
    color:#64748b;
    display:block;
    margin-bottom:6px;
}
.box strong{
    font-size:28px;
}
.table-wrap{
    overflow:auto;
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:12px 10px;
    border-bottom:1px solid #e5e7eb;
    font-size:14px;
    text-align:left;
}
th{
    background:#f1f5f9;
    font-size:13px;
    color:#475569;
}
.right{text-align:right;}
.center{text-align:center;}
.badge{
    background:#dcfce7;
    color:#166534;
    padding:4px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:700;
}
.empty{
    text-align:center;
    padding:20px;
    color:#64748b;
}
</style>

<div class="page">

    <div class="card">

        <div class="title">🚚 Delivery Report</div>
        <div class="sub">
            Monitoring of branch deliveries with D.R Number and received stocks
        </div>

        <div class="stats">
            <div class="box">
                <small>Total Deliveries</small>
                <strong>{{ $rows->count() }}</strong>
            </div>

            <div class="box">
                <small>Total Qty Delivered</small>
                <strong>{{ number_format($rows->sum('quantity')) }}</strong>
            </div>

            <div class="box">
                <small>Total Value</small>
                <strong>
                    ₱{{ number_format($rows->sum(fn($r) => $r->quantity * $r->unit_price),2) }}
                </strong>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <tr>
                    <th>Date</th>
                    <th>Branch</th>
                    <th>Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>D.R Number</th>
                    <th>Status</th>
                </tr>

                @forelse($rows as $row)
                <tr>
                    <td>{{ date('Y-m-d', strtotime($row->created_at)) }}</td>
                    <td>{{ $row->branch->name ?? '-' }}</td>
                    <td>{{ $row->product->name ?? '-' }}</td>
                    <td class="center">{{ $row->quantity }}</td>
                    <td class="right">₱{{ number_format($row->unit_price,2) }}</td>
                    <td class="right">
                        ₱{{ number_format($row->quantity * $row->unit_price,2) }}
                    </td>
                    <td>{{ $row->dr_number }}</td>
                    <td><span class="badge">Delivered</span></td>
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

    </div>

</div>

@endsection