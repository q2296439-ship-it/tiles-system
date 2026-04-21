@php
$layout = match(strtolower(auth()->user()->role)) {
    'admin'   => 'layouts.admin',
    'manager' => 'layouts.manager',
    'audit'   => 'layouts.manager',
    default   => 'layouts.cashier',
};

$routePrefix = match(strtolower(auth()->user()->role)) {
    'admin'   => 'admin',
    'manager' => 'manager',
    'audit'   => 'manager',
    default   => 'cashier',
};
@endphp

@extends($layout)

@section('content')

<style>
.wrap{
    max-width:1250px;
    margin:auto;
}
.card{
    background:#fff;
    border-radius:16px;
    padding:24px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}
.title{
    font-size:34px;
    font-weight:900;
    margin-bottom:14px;
    color:#0f172a;
}
.filter{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
    margin-bottom:18px;
}
.input{
    padding:10px 12px;
    border:1px solid #cbd5e1;
    border-radius:8px;
    font-size:14px;
    min-width:180px;
}
.btn{
    padding:10px 14px;
    border:none;
    border-radius:8px;
    background:#2563eb;
    color:#fff;
    font-weight:700;
    text-decoration:none;
    cursor:pointer;
    display:inline-block;
}
.btn-green{background:#16a34a;}
.top{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(260px,1fr));
    gap:14px;
    margin-bottom:18px;
}
.box{
    background:#eff6ff;
    border:1px solid #bfdbfe;
    border-radius:12px;
    padding:16px;
}
.label{
    font-size:13px;
    color:#475569;
    margin-bottom:6px;
}
.big{
    font-size:32px;
    font-weight:900;
}
.tbl{
    width:100%;
    border-collapse:separate;
    border-spacing:0;
}
.tbl th,
.tbl td{
    padding:12px;
    border-bottom:1px solid #e5e7eb;
    font-size:14px;
    text-align:center;
    vertical-align:middle;
}
.tbl th{
    background:#f8fafc;
    color:#475569;
    font-size:13px;
    font-weight:700;
}
.badge{
    padding:5px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
}
.saved{background:#dcfce7;color:#166534;}
.cancelled{background:#fee2e2;color:#991b1b;}
.returned{background:#ffedd5;color:#9a3412;}
.pagination{
    margin-top:16px;
    display:flex;
    justify-content:space-between;
    gap:10px;
}
.disabled{
    opacity:.5;
    pointer-events:none;
}
@media(max-width:768px){
    .filter{
        flex-direction:column;
        align-items:stretch;
    }
    .input,.btn{
        width:100%;
    }
}
</style>

<div class="wrap">
<div class="card">

<div class="title">📋 Delivery Report</div>

<form method="GET" class="filter">

<input type="date"
       name="date"
       value="{{ $selectedDate }}"
       class="input">

@if($role !== 'cashier')
<select name="branch_id" class="input">
    <option value="">All Branches</option>

    @foreach($branches as $branch)
        <option value="{{ $branch->id }}"
            {{ $selectedBranch == $branch->id ? 'selected' : '' }}>
            {{ $branch->name }}
        </option>
    @endforeach

</select>
@endif

<button class="btn">Filter</button>

<a href="/{{ $routePrefix }}/delivery-fee" class="btn">
➕ New Delivery
</a>

<a href="/{{ $routePrefix }}/delivery-today/excel?date={{ $selectedDate }}@if($role !== 'cashier')&branch_id={{ $selectedBranch }}@endif"
   class="btn btn-green">
📗 Excel
</a>

</form>

<div class="top">

<div class="box">
    <div class="label">Total Entries</div>
    <div class="big">{{ $totalCount }}</div>
</div>

<div class="box">
    <div class="label">Total Income</div>
    <div class="big">₱{{ number_format($totalIncome,2) }}</div>
</div>

</div>

<table class="tbl">

<thead>
<tr>
<th>#</th>
<th>Delivery No</th>
<th>Receipt No</th>
<th>Customer</th>
<th>Amount</th>
<th>Status</th>
<th>Date</th>
</tr>
</thead>

<tbody>

@forelse($rows as $row)
<tr>
<td>{{ $loop->iteration + (($rows->currentPage()-1) * $rows->perPage()) }}</td>
<td>{{ $row->delivery_no }}</td>
<td>{{ $row->receipt_no }}</td>
<td>{{ $row->customer_name }}</td>
<td>₱{{ number_format($row->amount,2) }}</td>
<td>
<span class="badge {{ $row->status }}">
{{ ucfirst($row->status) }}
</span>
</td>
<td>{{ $row->delivery_date }}</td>
</tr>
@empty
<tr>
<td colspan="7">No records found.</td>
</tr>
@endforelse

</tbody>
</table>

<div class="pagination">

@if($rows->onFirstPage())
<span class="btn disabled">⬅ Previous</span>
@else
<a href="{{ $rows->previousPageUrl() }}" class="btn">⬅ Previous</a>
@endif

@if($rows->hasMorePages())
<a href="{{ $rows->nextPageUrl() }}" class="btn">Next ➡</a>
@else
<span class="btn disabled">Next ➡</span>
@endif

</div>

</div>
</div>

@endsection