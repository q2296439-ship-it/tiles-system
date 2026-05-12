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

$displayCashIn = $todayCashIn;
$displayCashOut = $todayCashOut;
@endphp

@extends($layout)

@section('content')

<style>
.page{max-width:1150px;margin:auto;}
.card{
background:#dbeafe;
border:2px solid #93c5fd;
border-radius:16px;
padding:25px;
box-shadow:0 10px 25px rgba(0,0,0,.08);
}
.title{
text-align:center;
font-size:30px;
font-weight:900;
color:#0f172a;
letter-spacing:.5px;
}
.sub{
text-align:center;
font-size:12px;
color:#475569;
margin-top:4px;
margin-bottom:18px;
}
.toolbar{
display:flex;
justify-content:center;
margin-bottom:18px;
gap:10px;
flex-wrap:wrap;
}
.filter{
min-width:260px;
padding:10px 12px;
border:1px solid #334155;
border-radius:10px;
background:#fff;
font-size:14px;
outline:none;
}
.btn{
padding:10px 14px;
border:none;
border-radius:10px;
background:#2563eb;
color:#fff;
font-weight:700;
cursor:pointer;
}
.statement-date{
text-align:center;
font-size:13px;
font-weight:700;
color:#1e3a8a;
margin-bottom:18px;
}
.hero{
background:#fff;
border:1px solid #334155;
border-radius:14px;
padding:22px;
margin-bottom:18px;
}
.hero-label{
font-size:14px;
color:#64748b;
text-transform:uppercase;
letter-spacing:.5px;
text-align:center;
}
.hero-amount{
font-size:46px;
font-weight:900;
color:#16a34a;
margin-top:8px;
line-height:1;
text-align:center;
}
.hero-note{
font-size:12px;
color:#64748b;
margin-top:8px;
text-align:center;
}
.hero-breakdown{
margin-top:18px;
border-top:1px solid #e5e7eb;
padding-top:14px;
}
.hero-row{
display:flex;
justify-content:space-between;
padding:8px 0;
font-size:15px;
font-weight:700;
}
.hero-left{color:#334155;}
.hero-right{font-weight:900;}
.green{color:#15803d;}
.red{color:#b91c1c;}
.blue{color:#2563eb;}
.grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
gap:16px;
}
.box{
background:#fff;
border:1px solid #334155;
border-radius:14px;
padding:18px;
}
.box-title{
font-size:20px;
font-weight:900;
margin-bottom:12px;
color:#0f172a;
}
.row{
display:flex;
justify-content:space-between;
align-items:center;
padding:10px 0;
border-bottom:1px solid #e5e7eb;
font-size:14px;
}
.row:last-child{border-bottom:none;}
.label{color:#334155;}
.value{
font-weight:800;
font-size:15px;
}
.in{color:#15803d;}
.out{color:#b91c1c;}
.summary{
margin-top:18px;
background:#fff;
border:1px solid #334155;
border-radius:14px;
padding:18px;
}
.summary-title{
font-size:20px;
font-weight:900;
margin-bottom:10px;
color:#0f172a;
}
.net{
display:flex;
justify-content:space-between;
align-items:center;
font-size:18px;
font-weight:900;
padding-top:8px;
}
.net-value{
color:#2563eb;
font-size:28px;
}
.footer-note{
margin-top:15px;
font-size:12px;
color:#64748b;
text-align:center;
}
</style>

<div class="page">
<div class="card">

<div class="title">BRANCH CASH FLOW STATEMENT</div>
<div class="sub">Financial Position Report • Real-Time Business Monitoring</div>

<form method="GET" action="/{{ $routePrefix }}/total-cash">
<div class="toolbar">

@if($routePrefix !== 'cashier')
<select name="branch_id" class="filter">
<option value="">All Branches</option>
@foreach($branches as $branch)
<option value="{{ $branch->id }}" {{ $branchId == $branch->id ? 'selected' : '' }}>
{{ $branch->name }}
</option>
@endforeach
</select>
@endif

<input type="date"
       name="date"
       class="filter"
       value="{{ request('date', date('Y-m-d')) }}">

<button class="btn">
📅 Generate
</button>

<a href="/{{ $routePrefix }}/cash-flow-excel?branch_id={{ request('branch_id') }}&date={{ request('date', date('Y-m-d')) }}"
   class="btn"
   style="background:#16a34a;text-decoration:none;">
📗 Excel
</a>

</div>
</form>

<div class="statement-date">
As of {{ date('F d, Y') }}
</div>

<div class="hero">
<div class="hero-label">Available Cash</div>

<div class="hero-amount">
₱{{ number_format($totalCash,2) }}
</div>

<div class="hero-note">
Current usable cash balance of selected branch
</div>

<div class="hero-breakdown">

<div class="hero-row">
<span class="hero-left">Beginning Balance</span>
<span class="hero-right blue">₱{{ number_format($previousBalance,2) }}</span>
</div>

<div class="hero-row">
<span class="hero-left">Today Cash In</span>
<span class="hero-right green">₱{{ number_format($todayCashIn,2) }}</span>
</div>

<div class="hero-row">
<span class="hero-left">Today Cash Out</span>
<span class="hero-right red">₱{{ number_format($todayCashOut,2) }}</span>
</div>

<div class="hero-row" style="border-top:1px dashed #cbd5e1; margin-top:8px; padding-top:12px;">
<span class="hero-left">Running Balance</span>
<span class="hero-right blue">₱{{ number_format($totalCash,2) }}</span>
</div>

</div>
</div>

<div class="grid">

<div class="box">
<div class="box-title">💰 CASH IN</div>

<div class="row">
<span class="label">Actual Deposit Amount</span>
<span class="value in">₱{{ number_format($todayDeposit,2) }}</span>
</div>

<div class="row">
<span class="label">A/R Payment</span>
<span class="value in">₱{{ number_format($todayArPayments,2) }}</span>
</div>

<div class="row">
<span class="label">Incoming Transfers</span>
<span class="value in">₱{{ number_format($todayIncomingTransfers,2) }}</span>
</div>

<div class="row">
<span class="label"><strong>Total Cash In</strong></span>
<span class="value in">₱{{ number_format($displayCashIn,2) }}</span>
</div>
</div>

<div class="box">
<div class="box-title">💸 CASH OUT</div>

<div class="row">
<span class="label">Expenses</span>
<span class="value out">₱{{ number_format($todayExpenses,2) }}</span>
</div>

<div class="row">
<span class="label">Transfer to Other Branch</span>
<span class="value out">₱{{ number_format($todayOutgoingTransfers,2) }}</span>
</div>

<div class="row">
<span class="label"><strong>Total Cash Out</strong></span>
<span class="value out">₱{{ number_format($displayCashOut,2) }}</span>
</div>
</div>

</div>

<div class="summary">
<div class="summary-title">📊 NET CASH POSITION</div>

<div class="net">
<span>Beginning + Today In - Today Out</span>
<span class="net-value">₱{{ number_format($totalCash,2) }}</span>
</div>
</div>

<div class="footer-note">
This report resets daily activity totals while preserving running balance.
</div>

</div>
</div>

@endsection