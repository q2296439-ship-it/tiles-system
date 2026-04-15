@extends('layouts.manager')

@section('content')

<style>
.page{max-width:1400px;margin:auto;}
.topbar{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;margin-bottom:20px;}
.title{font-size:30px;font-weight:800;color:#0f172a;}
.subtitle{color:#64748b;font-size:14px;}
.actions{display:flex;gap:10px;flex-wrap:wrap;}
.card,.stat-card{
background:#fff;
border-radius:14px;
padding:18px;
box-shadow:0 6px 18px rgba(0,0,0,.05);
}
.stats{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
gap:15px;
margin-bottom:20px;
}
.stat-label{font-size:13px;color:#64748b;}
.stat-value{font-size:24px;font-weight:800;margin-top:6px;}
.filters{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:15px;}
.input,.select{
padding:10px 12px;
border:1px solid #d1d5db;
border-radius:10px;
}
.btn{
padding:10px 14px;
border:none;
border-radius:10px;
cursor:pointer;
font-weight:700;
font-size:14px;
text-decoration:none;
display:inline-block;
}
.btn-primary{background:#2563eb;color:#fff;}
.btn-success{background:#16a34a;color:#fff;}
.btn-danger{background:#dc2626;color:#fff;}
.btn-dark{background:#111827;color:#fff;}
.table-wrap{overflow:auto;}
.table{width:100%;border-collapse:collapse;}
.table th,.table td{
padding:13px;
border-bottom:1px solid #e5e7eb;
font-size:14px;
text-align:left;
}
.table th{background:#f8fafc;}
.badge{
padding:6px 10px;
border-radius:20px;
font-size:12px;
font-weight:700;
background:#fee2e2;
color:#991b1b;
}
.empty{text-align:center;padding:35px;color:#64748b;}
.pagination{
display:flex;
justify-content:space-between;
align-items:center;
margin-top:18px;
}
</style>

<div class="page">

<div class="topbar">
    <div>
        <div class="title">🔓 Request Access</div>
        <div class="subtitle">Manage closed deposits and reopen transactions</div>
    </div>

    <div class="actions">
        <a href="{{ route('manager.request.access.excel') }}" class="btn btn-success">Excel</a>
        <a href="{{ route('manager.request.access.pdf') }}" class="btn btn-dark">PDF</a>
    </div>
</div>

<div class="stats">
    <div class="stat-card">
        <div class="stat-label">Total Records</div>
        <div class="stat-value">{{ $closedDates->total() }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Showing</div>
        <div class="stat-value">{{ $closedDates->count() }}</div>
    </div>

    <div class="stat-card">
        <div class="stat-label">Page</div>
        <div class="stat-value">{{ $closedDates->currentPage() }}</div>
    </div>
</div>

<div class="card">

<div class="table-wrap">
<table class="table">
<thead>
<tr>
<th>#</th>
<th>Branch</th>
<th>Date Closed</th>
<th>Net</th>
<th>Actual</th>
<th>Variance</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>

<tbody>
@forelse($closedDates as $row)
<tr>
<td>{{ $loop->iteration }}</td>
<td>{{ $row->branch_name }}</td>
<td>{{ $row->deposit_date }}</td>
<td>₱{{ number_format($row->net_amount,2) }}</td>
<td>₱{{ number_format($row->actual_amount,2) }}</td>
<td>₱{{ number_format($row->variance,2) }}</td>
<td><span class="badge">Closed</span></td>
<td>
<form method="POST" action="{{ route('manager.request.open') }}">
@csrf
<input type="hidden" name="id" value="{{ $row->id }}">
<button class="btn btn-danger"
onclick="return confirm('Reopen transaction?')">
Reopen
</button>
</form>
</td>
</tr>
@empty
<tr>
<td colspan="8" class="empty">No records found.</td>
</tr>
@endforelse
</tbody>
</table>
</div>

<div class="pagination">
<div>
Showing {{ $closedDates->firstItem() ?? 0 }} - {{ $closedDates->lastItem() ?? 0 }}
</div>

<div>
@if($closedDates->onFirstPage())
<button class="btn" disabled>Previous</button>
@else
<a href="{{ $closedDates->previousPageUrl() }}" class="btn btn-primary">Previous</a>
@endif

@if($closedDates->hasMorePages())
<a href="{{ $closedDates->nextPageUrl() }}" class="btn btn-primary">Next</a>
@else
<button class="btn" disabled>Next</button>
@endif
</div>
</div>

</div>
</div>

@endsection