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
margin-top:15px;
font-size:14px;
}
.table th,.table td{
padding:10px;
border-bottom:1px solid #e5e7eb;
text-align:left;
vertical-align:top;
}
.table th{
font-weight:800;
color:#0f172a;
}
.table tr:hover{
background:#f8fafc;
}
.table th:last-child,
.table td:last-child{
text-align:right;
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
</style>

<div class="page">
<div class="card">

@if(session('success'))
<div class="alert-success" id="successAlert">
    {{ session('success') }}
</div>
@endif

<div class="title">STORE EXPENSES</div>
<div class="sub">Connected to Cash Flow • Daily Expense Monitoring</div>

<form method="POST" action="{{ route('cashier.expenses.store') }}">
@csrf

<div class="grid">
    <input type="date"
           name="expense_date"
           class="input"
           value="{{ date('Y-m-d') }}"
           required>

    <select name="category_id" class="input" required>
        <option value="">Select Category</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
        @endforeach
    </select>

    <input type="number"
           step="0.01"
           min="0"
           name="amount"
           class="input"
           placeholder="Expense Amount"
           required>

    <select name="payment_method" class="input">
        <option value="cash">Cash</option>
        <option value="bank">Bank</option>
        <option value="gcash">GCash</option>
    </select>
</div>

<div style="margin-top:12px;">
    <textarea name="description"
              class="input"
              placeholder="Notes / Instructions"></textarea>
</div>

<div class="actions">
    <button type="submit" class="btn btn-green">
        💾 Save Expense
    </button>

    <button type="reset" class="btn btn-red">
        ❌ Clear
    </button>

    <a href="{{ route('cashier.expenses.list') }}" class="btn btn-blue">
        📋 Expense List
    </a>
</div>

<div class="box" id="records">

    <div>Today Expense Total</div>
    <div class="total">
        ₱{{ number_format($totalToday ?? 0,2) }}
    </div>
    <div class="note">
        Saved = recorded daily expense
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Date</th>
                <th>Category</th>
                <th>Description</th>
                <th>Amount</th>
            </tr>
        </thead>
        <tbody>
        @forelse($expenses as $row)
            <tr>
                <td>{{ $row->expense_date }}</td>
                <td>{{ $row->category->name ?? '' }}</td>
                <td>{{ $row->description }}</td>
                <td class="amount">₱{{ number_format($row->amount,2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="empty">No expense records found</td>
            </tr>
        @endforelse
        </tbody>
    </table>

</div>
</form>

</div>
</div>

@if(session('success'))
<script>
setTimeout(function(){
    let alertBox = document.getElementById('successAlert');
    if(alertBox){
        alertBox.style.display = 'none';
    }
}, 2500);
</script>
@endif

@endsection