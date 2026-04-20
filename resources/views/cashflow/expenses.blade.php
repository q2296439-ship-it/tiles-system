@extends('layouts.admin')

@section('content')

<div class="container">
    <h2>Store Expenses</h2>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('cashier.expenses.store') }}" method="POST">
        @csrf

        <div>
            <label>Date</label>
            <input type="date" name="expense_date" required>
        </div>

        <div>
            <label>Category</label>
            <select name="category_id" required>
                <option value="">Select Category</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label>Description</label>
            <input type="text" name="description">
        </div>

        <div>
            <label>Amount</label>
            <input type="number" step="0.01" name="amount" required>
        </div>

        <div>
            <label>Payment Method</label>
            <select name="payment_method">
                <option value="cash">Cash</option>
                <option value="bank">Bank</option>
                <option value="gcash">GCash</option>
            </select>
        </div>

        <button type="submit">Save Expense</button>
    </form>

    <hr>

    <table border="1" width="100%">
        <tr>
            <th>Date</th>
            <th>Category</th>
            <th>Description</th>
            <th>Amount</th>
        </tr>

        @foreach($expenses as $row)
        <tr>
            <td>{{ $row->expense_date }}</td>
            <td>{{ $row->category->name ?? '' }}</td>
            <td>{{ $row->description }}</td>
            <td>{{ number_format($row->amount,2) }}</td>
        </tr>
        @endforeach
    </table>
</div>

@endsection