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
.page{max-width:1100px;margin:auto;}
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
.grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
gap:12px;
}
.input{
width:100%;
padding:10px 12px;
border:1px solid #334155;
border-radius:8px;
background:#fff;
font-size:14px;
outline:none;
}
.input:focus{
border-color:#2563eb;
box-shadow:0 0 0 2px rgba(37,99,235,.15);
}
textarea.input{
height:100px;
resize:none;
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
.btn:hover{opacity:.92;transform:translateY(-1px);}
.btn-blue{background:#2563eb;color:#fff;}
.btn-green{background:#16a34a;color:#fff;}
.btn-red{background:#dc2626;color:#fff;}
.btn-orange{background:#ea580c;color:#fff;}
.actions{
display:flex;
gap:10px;
flex-wrap:wrap;
margin-top:15px;
}
.box{
margin-top:15px;
padding:14px;
border:1px solid #334155;
border-radius:10px;
background:#fff;
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
.alert-success{
background:#dcfce7;
color:#166534;
padding:12px;
border-radius:8px;
margin-bottom:15px;
}
.table{
width:100%;
border-collapse:collapse;
margin-top:10px;
font-size:14px;
}
.table th,.table td{
padding:10px;
border-bottom:1px solid #e5e7eb;
text-align:left;
white-space:nowrap;
}
.table th{
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
padding:15px;
}
.section-title{
font-size:18px;
font-weight:800;
margin-bottom:8px;
color:#0f172a;
}
.inline-form{display:inline;}
</style>

<div class="page">
<div class="card">

@if(session('success'))
<div class="alert-success">{{ session('success') }}</div>
@endif

<div class="title">TRANSFER CASH TO OTHER BRANCH</div>
<div class="sub">Outgoing / Incoming Cash Transfer Monitoring</div>

<form method="GET" action="/{{ $routePrefix }}/cash-transfer">
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

<button class="btn btn-blue">Filter</button>

</div>
</form>

<form method="POST" action="/{{ $routePrefix }}/cash-transfer/store">
@csrf

<div class="grid" style="margin-top:12px;">

<input type="date"
name="transfer_date"
class="input"
value="{{ date('Y-m-d') }}"
required>

<select name="to_branch_id" class="input" required>
<option value="">Select Receiving Branch</option>
@foreach($receiverBranches as $branch)
<option value="{{ $branch->id }}">{{ $branch->name }}</option>
@endforeach
</select>

<input type="number"
step="0.01"
min="1"
name="amount"
class="input"
placeholder="Transfer Amount"
required>

<input type="text"
name="notes"
class="input"
placeholder="Notes / Reason">

</div>

<div class="actions">
<button type="submit" class="btn btn-green">
💸 Send Cash
</button>

<button type="reset" class="btn btn-red">
❌ Clear
</button>
</div>

</form>

<div class="box">
<div>Today Total Sent</div>
<div class="total">
₱{{ number_format($totalToday ?? 0,2) }}
</div>
<div class="note">
Counted only completed accepted transfers
</div>
</div>

<div class="box">
<div class="section-title">OUTGOING</div>

<div style="overflow-x:auto;">
<table class="table">
<thead>
<tr>
<th>Date</th>
<th>From</th>
<th>To</th>
<th>Amount</th>
<th>Status</th>
<th>Encoded By</th>
</tr>
</thead>
<tbody>

@forelse($outgoing as $row)
<tr>
<td>{{ $row->transfer_date }}</td>
<td>{{ $row->fromBranch->name ?? '' }}</td>
<td>{{ $row->toBranch->name ?? '' }}</td>
<td class="amount">₱{{ number_format($row->amount,2) }}</td>
<td>{{ strtoupper($row->status) }}</td>
<td>{{ $row->user->name ?? '' }}</td>
</tr>
@empty
<tr>
<td colspan="6" class="empty">No outgoing transfers</td>
</tr>
@endforelse

</tbody>
</table>
</div>
</div>

<div class="box">
<div class="section-title">INCOMING</div>

<div style="overflow-x:auto;">
<table class="table">
<thead>
<tr>
<th>Date</th>
<th>From Branch</th>
<th>Amount</th>
<th>Notes</th>
<th>Action</th>
</tr>
</thead>
<tbody>

@forelse($incoming as $row)
<tr>
<td>{{ $row->transfer_date }}</td>
<td>{{ $row->fromBranch->name ?? '' }}</td>
<td class="amount">₱{{ number_format($row->amount,2) }}</td>
<td>{{ $row->notes }}</td>
<td>
@if($role === 'cashier')
<form method="POST"
action="/{{ $routePrefix }}/cash-transfer/{{ $row->id }}/accept"
class="inline-form">
@csrf
<button type="submit" class="btn btn-orange">
✅ Accept
</button>
</form>
@else
<span style="font-weight:700;color:#64748b;">Pending</span>
@endif
</td>
</tr>
@empty
<tr>
<td colspan="5" class="empty">No incoming pending transfers</td>
</tr>
@endforelse

</tbody>
</table>
</div>
</div>

</div>
</div>

@endsection