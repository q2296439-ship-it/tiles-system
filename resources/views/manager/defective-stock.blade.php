@extends('layouts.manager')

@section('content')

<style>
.page{max-width:1200px;margin:auto;}
.card{
    background:#fff;
    padding:25px;
    border-radius:14px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}
.grid{
    display:grid;
    grid-template-columns:380px 1fr;
    gap:20px;
}
label{
    font-size:13px;
    color:#475569;
    margin-bottom:5px;
    display:block;
}
input,select,textarea{
    width:100%;
    padding:10px;
    margin-bottom:14px;
    border:1px solid #cbd5e1;
    border-radius:8px;
}
.btn{
    width:100%;
    padding:11px;
    border:none;
    border-radius:8px;
    background:#dc2626;
    color:#fff;
    font-weight:700;
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:12px;
    border-bottom:1px solid #e5e7eb;
    font-size:14px;
}
th{
    background:#f8fafc;
}
.success{background:#dcfce7;padding:10px;border-radius:8px;margin-bottom:10px;}
.error{background:#fee2e2;padding:10px;border-radius:8px;margin-bottom:10px;}
</style>

<div class="page">

<div class="card">

<h2>🛠️ Defective Stock</h2>
<p style="color:#64748b;margin-bottom:20px;">
Write-off damaged / defective stocks from inventory
</p>

@if(session('success'))
<div class="success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="error">{{ session('error') }}</div>
@endif

<div class="grid">

<div>
<form method="POST" action="{{ route('manager.defective.store') }}">
@csrf

<label>Product</label>
<select name="product_id" required>
<option value="">-- Select Product --</option>
@foreach($products as $product)
<option value="{{ $product->id }}">
{{ $product->name }} - {{ $product->size }}
(Stock: {{ $product->stock }})
</option>
@endforeach
</select>

<label>Quantity</label>
<input type="number" name="quantity" min="1" required>

<label>Reason</label>
<textarea name="reason" rows="4" required></textarea>

<button class="btn">Save Defective</button>

</form>
</div>

<div>

<table>
<tr>
<th>Date</th>
<th>Product</th>
<th>Branch</th>
<th>Qty</th>
<th>Reason</th>
</tr>

@foreach($rows as $row)
<tr>
<td>{{ $row->created_at->format('Y-m-d') }}</td>
<td>{{ $row->product->name ?? '-' }}</td>
<td>{{ $row->branch->name ?? '-' }}</td>
<td>{{ $row->quantity }}</td>
<td>{{ $row->reason }}</td>
</tr>
@endforeach

</table>

<div style="margin-top:15px;">
{{ $rows->links() }}
</div>

</div>

</div>
</div>
</div>

@endsection