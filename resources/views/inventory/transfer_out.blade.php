@extends('layouts.admin')

@section('content')

<style>
.container { max-width: 700px; margin:auto; }

.card {
    background:white;
    padding:25px;
    border-radius:12px;
    box-shadow:0 4px 10px rgba(0,0,0,0.05);
}

h2 { margin-bottom:20px; }

.form-group {
    margin-bottom:15px;
}

label {
    display:block;
    margin-bottom:5px;
    font-weight:500;
}

input, select {
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:6px;
}

.btn-submit {
    width:100%;
    padding:12px;
    background:#2563eb;
    color:white;
    border:none;
    border-radius:6px;
    cursor:pointer;
    font-weight:bold;
}

.btn-submit:hover {
    background:#1d4ed8;
}
</style>

<div class="container">

<div class="card">

<h2>🔄 Transfer Out Request</h2>

<form method="POST" action="/admin/transfer-out">
@csrf

<div class="form-group">
<label>Product</label>
<select name="product_id" required>
    <option value="">-- Select Product --</option>
    @foreach($products as $product)
        <option value="{{ $product->id }}">{{ $product->name }}</option>
    @endforeach
</select>
</div>

<div class="form-group">
<label>From Branch</label>
<select name="from_branch_id" required>
    @foreach($branches as $branch)
        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
    @endforeach
</select>
</div>

<div class="form-group">
<label>To Branch</label>
<select name="to_branch_id" required>
    <option value="">-- Select Branch --</option>
    @foreach($branches as $branch)
        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
    @endforeach
</select>
</div>

<div class="form-group">
<label>Quantity</label>
<input type="number" name="quantity" min="1" required>
</div>

<button type="submit" class="btn-submit">
    Submit for Approval
</button>

</form>

</div>

</div>

@endsection