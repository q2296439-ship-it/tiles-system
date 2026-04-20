@extends('layouts.cashier')

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
.btn:hover{opacity:.92;transform:translateY(-1px);}
.btn-blue{background:#2563eb;color:#fff;}
.btn-green{background:#16a34a;color:#fff;}
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
.table th,.table td{
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
</style>

<div class="page">
<div class="card">

<div class="title">EXPENSE LIST</div>
<div class="sub">Daily Expense Records • Branch Monitoring</div>

<div class="actions">
    <a href="{{ route('cashier.expenses') }}" class="btn btn-blue">
        ➕ Add Expense
    </a>

    <a href="{{ route('cashier.expenses.excel') }}" class="btn btn-green">
        📥 Download Excel
    </a>
</div>

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
                    <td class="amount">₱{{ number_format($row->amount,2) }}</td>
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
</div>

</div>
</div>

@endsection