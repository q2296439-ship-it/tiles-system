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
.page{
    max-width:1100px;
    margin:auto;
}

.card{
    background:#dbeafe;
    border:2px solid #93c5fd;
    border-radius:14px;
    padding:25px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}

.title{
    text-align:center;
    font-size:28px;
    font-weight:900;
    color:#0f172a;
}

.sub{
    text-align:center;
    font-size:12px;
    color:#475569;
    margin-bottom:15px;
}

.actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-bottom:15px;
}

.btn{
    border:none;
    padding:11px 16px;
    border-radius:10px;
    cursor:pointer;
    font-weight:700;
    text-decoration:none;
    display:inline-block;
    text-align:center;
    transition:.2s ease;
}

.btn:hover{
    opacity:.92;
    transform:translateY(-1px);
}

.btn-blue{
    background:#2563eb;
    color:#fff;
}

.btn-green{
    background:#16a34a;
    color:#fff;
}

.input{
    padding:10px 12px;
    border:1px solid #334155;
    border-radius:8px;
    background:#fff;
    font-size:14px;
    min-width:220px;
    outline:none;
}

.box{
    padding:14px;
    border:1px solid #334155;
    border-radius:10px;
    background:#fff;
    margin-bottom:15px;
}

.total{
    font-size:26px;
    font-weight:900;
    color:#0f172a;
}

.note{
    font-size:12px;
    color:#64748b;
    margin-top:4px;
}

.table{
    width:100%;
    border-collapse:collapse;
    font-size:14px;
    background:#fff;
    border-radius:10px;
    overflow:hidden;
}

.table th,
.table td{
    padding:10px;
    border-bottom:1px solid #e5e7eb;
    text-align:left;
    white-space:nowrap;
}

.table th{
    background:#eff6ff;
    font-weight:800;
    color:#0f172a;
}

.table th:last-child,
.table td:last-child{
    text-align:right;
}

.table tr:hover{
    background:#f8fafc;
}

.amount{
    font-weight:800;
    color:#b91c1c;
}

.empty{
    text-align:center;
    color:#64748b;
    padding:20px;
}

/* PAGINATION */
.pagination-wrapper{
    margin-top:20px;
    display:flex;
    justify-content:center;
}

.pagination{
    display:flex;
    gap:8px;
    flex-wrap:wrap;
    list-style:none;
    padding:0;
}

.pagination li a,
.pagination li span{
    padding:8px 14px;
    border-radius:8px;
    border:1px solid #cbd5e1;
    background:#fff;
    color:#0f172a;
    text-decoration:none;
    font-weight:700;
}

.pagination li.active span{
    background:#2563eb;
    color:#fff;
    border-color:#2563eb;
}

.pagination li a:hover{
    background:#eff6ff;
}
</style>

<div class="page">
<div class="card">

<div class="title">EXPENSE LIST</div>

<div class="sub">
    Daily Expense Records • Branch Monitoring
</div>

<form method="GET" action="/{{ $routePrefix }}/store-expenses/list">

<div class="actions">

@if($role !== 'cashier')

<select name="branch_id" class="input">

<option value="">All Branches</option>

@foreach($branches as $branch)

<option value="{{ $branch->id }}"
    {{ $branchId == $branch->id ? 'selected' : '' }}>

    {{ $branch->name }}

</option>

@endforeach

</select>

@endif

<button class="btn btn-blue">
    Filter
</button>

<a href="/{{ $routePrefix }}/store-expenses"
   class="btn btn-blue">

    ➕ Add Expense

</a>

<a href="/{{ $routePrefix }}/store-expenses/excel@if($role !== 'cashier' && $branchId)?branch_id={{ $branchId }}@endif"
   class="btn btn-green">

    📥 Download Excel

</a>

</div>
</form>

<div class="box">

<div>Today Expense Total</div>

<div class="total">
    ₱{{ number_format($totalToday ?? 0,2) }}
</div>

<div class="note">
    Showing latest records first
</div>

</div>

<div class="box">

<div style="overflow-x:auto;">

<table class="table">

<thead>

<tr>
<th>Date</th>
<th>Branch</th>
<th>Category</th>
<th>Description</th>
<th>Encoded By</th>
<th>Payment</th>
<th>Amount</th>
</tr>

</thead>

<tbody>

@forelse($expenses as $row)

<tr>

<td>{{ $row->expense_date }}</td>

<td>{{ $row->branch->name ?? '' }}</td>

<td>{{ $row->category->name ?? '' }}</td>

<td>{{ $row->description }}</td>

<td>{{ $row->user->name ?? '' }}</td>

<td>{{ strtoupper($row->payment_method) }}</td>

<td class="amount">
    ₱{{ number_format($row->amount,2) }}
</td>

</tr>

@empty

<tr>
<td colspan="7" class="empty">
    No expense records found
</td>
</tr>

@endforelse

</tbody>

</table>

</div>

{{-- PAGINATION --}}
<div class="pagination-wrapper">
    {{ $expenses->links() }}
</div>

</div>

</div>
</div>

<script>
setInterval(function () {
    location.reload();
}, 10000);
</script>

@endsection