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

@section('title', 'A/R Accounts')

@section('content')

<style>
.ar-wrap{
    max-width:1150px;
    margin:auto;
    padding:10px;
}
.ar-card{
    background:#fff;
    border-radius:16px;
    padding:24px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}
.ar-title{
    font-size:34px;
    font-weight:900;
    color:#0f172a;
    margin-bottom:4px;
}
.ar-sub{
    color:#64748b;
    font-size:14px;
    margin-bottom:18px;
}
.ar-filter{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(180px,1fr));
    gap:10px;
    margin-bottom:18px;
}
.ar-input{
    width:100%;
    padding:10px 12px;
    border:1px solid #cbd5e1;
    border-radius:10px;
    font-size:14px;
}
.ar-btn{
    padding:10px 14px;
    border:none;
    border-radius:10px;
    font-weight:700;
    color:#fff;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
    text-align:center;
}
.blue{background:#2563eb;}
.gray{background:#6b7280;}
.green{background:#16a34a;}
.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
    gap:14px;
    margin-bottom:18px;
}
.stat-box{
    background:#f8fafc;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:16px;
}
.stat-box small{
    display:block;
    color:#64748b;
    margin-bottom:6px;
}
.stat-box strong{
    font-size:32px;
}
.tbl-wrap{overflow:auto;}
.tbl{
    width:100%;
    border-collapse:collapse;
}
.tbl th,.tbl td{
    padding:12px;
    border-bottom:1px solid #e5e7eb;
    text-align:center;
    font-size:14px;
}
.tbl th{
    background:#f8fafc;
    font-size:13px;
    color:#475569;
}
.badge{
    padding:6px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
}
.pending{
    background:#fef3c7;
    color:#92400e;
}
.alert-success{
    background:#dcfce7;
    color:#166534;
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
}
.alert-error{
    background:#fee2e2;
    color:#991b1b;
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
}
.pagination{
    margin-top:18px;
}
.modal-box{
    background:#fff;
    padding:18px;
    border-radius:14px;
}
</style>

<div class="ar-wrap">
<div class="ar-card">

<div class="ar-title">📒 A/R Accounts</div>
<div class="ar-sub">Pending DP / Partial customer balances</div>

<form method="GET" action="/{{ $routePrefix }}/ar-accounts">
<div class="ar-filter">

<input type="date"
       name="date"
       class="ar-input"
       value="{{ request('date') }}">

<input type="text"
       name="search"
       class="ar-input"
       placeholder="Search customer / receipt"
       value="{{ request('search') }}">

@if($role !== 'cashier')
<select name="branch_id" class="ar-input">
    <option value="">All Branches</option>
    @foreach($branches as $branch)
        <option value="{{ $branch->id }}"
            {{ $selectedBranch == $branch->id ? 'selected' : '' }}>
            {{ $branch->name }}
        </option>
    @endforeach
</select>
@endif

<button class="ar-btn blue">Filter</button>

<a href="/{{ $routePrefix }}/ar-accounts" class="ar-btn gray">
Reset
</a>

</div>
</form>

<div class="stats">

<div class="stat-box">
<small>Pending Accounts</small>
<strong style="color:#2563eb;">{{ $rows->total() }}</strong>
</div>

<div class="stat-box">
<small>Total Receivable</small>
<strong style="color:#16a34a;">
₱{{ number_format($rows->sum('balance'),2) }}
</strong>
</div>

</div>

@if(session('success'))
<div class="alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert-error">{{ session('error') }}</div>
@endif

<div class="tbl-wrap">
<table class="tbl">

<thead>
<tr>
<th>#</th>
<th>Receipt No</th>
<th>Date</th>
<th>Customer</th>
<th>Total</th>
<th>Paid</th>
<th>Balance</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>

@forelse($rows as $row)
<tr>
<td>{{ $loop->iteration + (($rows->currentPage()-1)*$rows->perPage()) }}</td>
<td>{{ $row->receipt_no }}</td>
<td>{{ date('Y-m-d', strtotime($row->receipt_date)) }}</td>
<td>{{ $row->customer_name }}</td>
<td>₱{{ number_format($row->total_amount,2) }}</td>
<td style="color:#16a34a;font-weight:700;">
₱{{ number_format($row->paid_amount,2) }}
</td>
<td style="color:#dc2626;font-weight:800;">
₱{{ number_format($row->balance,2) }}
</td>
<td>
<span class="badge pending">Pending</span>
</td>
<td>
<button class="ar-btn green"
        data-bs-toggle="modal"
        data-bs-target="#payModal{{ $row->id }}">
💵 Payment
</button>
</td>
</tr>

<div class="modal fade" id="payModal{{ $row->id }}" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">
<form method="POST" action="/{{ $routePrefix }}/ar-payment/{{ $row->id }}">
@csrf

<div class="modal-content border-0 rounded-4 shadow">
<div class="modal-header">
<h5 class="modal-title">Payment - {{ $row->receipt_no }}</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<label>Balance</label>
<input type="text"
       class="ar-input"
       value="₱{{ number_format($row->balance,2) }}"
       readonly>

<br>

<label>Payment</label>
<input type="number"
       name="payment"
       step="0.01"
       max="{{ $row->balance }}"
       class="ar-input"
       required>

<br>

<label>Date</label>
<input type="date"
       name="payment_date"
       value="{{ date('Y-m-d') }}"
       class="ar-input">

<br>

<label>Notes</label>
<textarea name="notes"
          rows="3"
          class="ar-input"></textarea>

</div>

<div class="modal-footer">
<button class="ar-btn green" style="width:100%;">
Save Payment
</button>
</div>

</div>
</form>
</div>
</div>

@empty
<tr>
<td colspan="9">No pending accounts found.</td>
</tr>
@endforelse

</tbody>
</table>
</div>

<div class="pagination">
{{ $rows->links() }}
</div>

</div>
</div>

@endsection