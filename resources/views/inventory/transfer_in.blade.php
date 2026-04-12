@extends('layouts.admin')

@section('content')

<style>
    .card {
        background: white;
        padding: 25px;
        border-radius: 12px;
        max-width: 600px;
        margin: auto;
        box-shadow: 0 5px 20px rgba(0,0,0,0.08);
    }

    h2 {
        margin-bottom: 20px;
    }

    label {
        font-size: 13px;
        color: #475569;
        display: block;
        margin-bottom: 5px;
    }

    input, select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        font-size: 14px;
    }

    .btn {
        background: #f59e0b;
        color: white;
        padding: 10px;
        border: none;
        width: 100%;
        border-radius: 6px;
        cursor: pointer;
    }

    .success {
        background: #dcfce7;
        color: #166534;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 15px;
    }

    .error {
        background: #fee2e2;
        color: #991b1b;
        padding: 10px;
        border-radius: 6px;
        margin-bottom: 15px;
    }
</style>

<div class="card">

    <h2>🔄 Transfer In Request</h2>

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="success">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR --}}
    @if($errors->any())
        <div class="error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('inventory.transfer.store') }}">
        @csrf

        {{-- PRODUCT --}}
        <label>Product</label>
        <select name="product_id" required>
            <option value="">-- Select Product --</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}">
                    {{ $product->name }}
                </option>
            @endforeach
        </select>

        {{-- FROM --}}
        <label>From Branch</label>
        <select name="from_branch" required>
            <option value="">-- Select Branch --</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>

        {{-- TO --}}
        <label>To Branch</label>
        <select name="to_branch" required>
            <option value="">-- Select Branch --</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>

        {{-- QTY --}}
        <label>Quantity</label>
        <input type="number" name="quantity" required>

        <button type="submit" class="btn">
            Submit for Approval
        </button>
    </form>

</div>

@endsection