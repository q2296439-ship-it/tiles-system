@extends('layouts.admin')

@section('content')

<style>
.page-wrap{
    max-width: 760px;
    margin: auto;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    margin-bottom:22px;
    flex-wrap:wrap;
}

.title{
    font-size:26px;
    font-weight:700;
    color:#111827;
    margin:0;
}

.back-btn{
    text-decoration:none;
    background:#e5e7eb;
    color:#111827;
    padding:10px 14px;
    border-radius:10px;
    font-size:14px;
    font-weight:600;
    transition:.2s;
}

.back-btn:hover{
    background:#d1d5db;
}

.card{
    background:#ffffff;
    border-radius:16px;
    padding:28px;
    box-shadow:0 8px 24px rgba(0,0,0,0.06);
}

.subtitle{
    font-size:14px;
    color:#6b7280;
    margin-bottom:22px;
}

.error-box{
    background:#fef2f2;
    border:1px solid #fecaca;
    color:#991b1b;
    padding:14px 16px;
    border-radius:12px;
    margin-bottom:20px;
}

.error-box ul{
    margin:0;
    padding-left:18px;
}

.grid{
    display:grid;
    grid-template-columns:1fr 1fr;
    gap:18px;
}

.form-group{
    display:flex;
    flex-direction:column;
}

.full{
    grid-column:1 / -1;
}

label{
    font-size:13px;
    font-weight:600;
    color:#374151;
    margin-bottom:7px;
}

input,
select{
    width:100%;
    padding:12px 14px;
    border:1px solid #d1d5db;
    border-radius:10px;
    font-size:14px;
    outline:none;
    transition:.2s;
    box-sizing:border-box;
    background:#fff;
}

input:focus,
select:focus{
    border-color:#3b82f6;
    box-shadow:0 0 0 3px rgba(59,130,246,.10);
}

.actions{
    margin-top:25px;
    display:flex;
    gap:12px;
    flex-wrap:wrap;
}

.btn{
    border:none;
    padding:12px 18px;
    border-radius:10px;
    font-size:14px;
    font-weight:700;
    cursor:pointer;
    transition:.2s;
    text-decoration:none;
}

.btn-save{
    background:#16a34a;
    color:#fff;
}

.btn-save:hover{
    background:#15803d;
}

.btn-cancel{
    background:#e5e7eb;
    color:#111827;
}

.btn-cancel:hover{
    background:#d1d5db;
}

.note{
    margin-top:18px;
    font-size:12px;
    color:#6b7280;
}

@media (max-width: 768px){
    .grid{
        grid-template-columns:1fr;
    }
}
</style>

<div class="page-wrap">

    <div class="top-bar">
        <h2 class="title">➕ Add Product</h2>
        <a href="/admin/products" class="back-btn">← Back to Products</a>
    </div>

    <div class="card">

        <div class="subtitle">
            Fill in the product details below to add a new item to your inventory.
        </div>

        @if($errors->any())
            <div class="error-box">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="/admin/products">
            @csrf

            <div class="grid">

                <div class="form-group">
                    <label>SKU</label>
                    <input type="text" name="sku" value="{{ old('sku') }}" required>
                </div>

                <div class="form-group">
                    <label>Category</label>
                    <input type="text" name="category" value="{{ old('category') }}" required>
                </div>

                <div class="form-group full">
                    <label>Product Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label>Size</label>
                    <input type="text" name="size" value="{{ old('size') }}">
                </div>

                <div class="form-group">
                    <label>Color</label>
                    <input type="text" name="color" value="{{ old('color') }}">
                </div>

                <div class="form-group">
                    <label>Price</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price') }}" required>
                </div>

                <div class="form-group">
                    <label>Stock</label>
                    <input type="number" name="stock" value="{{ old('stock') }}" required>
                </div>

                <div class="form-group">
                    <label>Low Stock Threshold</label>
                    <input type="number" name="low_stock_threshold" value="{{ old('low_stock_threshold', 10) }}">
                </div>

                <div class="form-group">
                    <label>Select Branch</label>
                    <select name="branch_id" required>
                        <option value="">-- Select Branch --</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}"
                                {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="actions">
                <button type="submit" class="btn btn-save">💾 Save Product</button>
                <a href="/admin/products" class="btn btn-cancel">Cancel</a>
            </div>

            <div class="note">
                Tip: Make sure SKU is unique and branch is correctly assigned.
            </div>

        </form>

    </div>

</div>

@endsection