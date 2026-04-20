@extends('layouts.cashier')

@section('content')

<style>
.wrap{max-width:1200px;margin:auto;}
.card{
background:#fff;
border-radius:14px;
padding:20px;
box-shadow:0 10px 25px rgba(0,0,0,.08);
}
.top{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:12px;
margin-bottom:15px;
}
.box{
background:#eff6ff;
border:1px solid #bfdbfe;
border-radius:12px;
padding:15px;
}
.big{font-size:28px;font-weight:900;}
.tbl{
width:100%;
border-collapse:collapse;
margin-top:10px;
}
.tbl th,.tbl td{
padding:10px;
border-bottom:1px solid #e5e7eb;
font-size:14px;
text-align:left;
}
.tbl th{background:#f8fafc;}
.badge{
padding:5px 9px;
border-radius:999px;
font-size:12px;
font-weight:700;
}
.saved{background:#dcfce7;color:#166534;}
.cancelled{background:#fee2e2;color:#991b1b;}
.returned{background:#ffedd5;color:#9a3412;}
.filter{
display:flex;
gap:10px;
margin-bottom:15px;
flex-wrap:wrap;
align-items:center;
}
.input{
padding:10px;
border:1px solid #cbd5e1;
border-radius:8px;
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
.pagination{
margin-top:15px;
display:flex;
justify-content:space-between;
gap:10px;
}
.disabled{
opacity:.5;
pointer-events:none;
}
</style>

<div class="wrap">
<div class="card">

<h2>📋 Delivery Report</h2>

<form method="GET" class="filter">
<input type="date" name="date" value="{{ $selectedDate }}" class="input">

<button class="btn">Filter</button>

<a href="{{ route('cashier.delivery.fee') }}" class="btn">
➕ New Delivery
</a>

<a href="{{ route('cashier.delivery.today') }}?date={{ $selectedDate }}&export=excel"
class="btn btn-green">
📗 Excel
</a>
</form>

<div class="top">
<div class="box">
<div>Total Entries</div>
<div class="big">{{ $totalCount }}</div>
</div>

<div class="box">
<div>Total Income</div>
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
{{ $row->status }}
</span>
</td>
<td>{{ $row->delivery_date }}</td>
</tr>
@empty
<tr>
<td colspan="7" style="text-align:center;">No records found.</td>
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