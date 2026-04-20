@extends('layouts.cashier')

@section('content')
<div class="max-w-5xl mx-auto mt-5 mb-8">

    <div class="rounded-2xl border p-5 shadow"
         style="background:#dbeafe; border-color:#60a5fa;">

        <div class="text-center mb-4">
            <h1 class="text-3xl font-bold text-gray-800">STORE EXPENSES</h1>
            <p class="text-xs text-gray-600">Connected to Cash Flow • Daily Expense Monitoring</p>
        </div>

        @if(session('success'))
            <div class="mb-3 px-4 py-2 rounded-lg text-white bg-green-600">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('cashier.expenses.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-4 gap-2 mb-2">
                <input type="date" name="expense_date"
                    value="{{ date('Y-m-d') }}"
                    class="border rounded-lg px-3 py-2 w-full">

                <select name="category_id"
                    class="border rounded-lg px-3 py-2 w-full">
                    <option value="">Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <input type="number" step="0.01" name="amount"
                    placeholder="Expense Amount"
                    class="border rounded-lg px-3 py-2 w-full">

                <select name="payment_method"
                    class="border rounded-lg px-3 py-2 w-full">
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                    <option value="gcash">GCash</option>
                </select>
            </div>

            <div class="mb-3">
                <textarea name="description" rows="4"
                    placeholder="Notes / Instructions"
                    class="border rounded-lg px-3 py-2 w-full"></textarea>
            </div>

            <div class="flex gap-2 mb-3">
                <button class="bg-green-600 text-white px-5 py-2 rounded-lg font-semibold">
                    💾 Save Expense
                </button>

                <a href="{{ route('cashier.expenses') }}"
                   class="bg-red-500 text-white px-5 py-2 rounded-lg font-semibold">
                    ✖ Cancel
                </a>

                <a href="#records"
                   class="bg-blue-600 text-white px-5 py-2 rounded-lg font-semibold">
                    📋 Expense List
                </a>
            </div>
        </form>

        <div id="records" class="bg-white rounded-xl border p-4">
            <div class="mb-2">
                <p class="text-sm text-gray-600">Today Expense Total</p>
                <h2 class="text-4xl font-bold text-red-600">
                    ₱{{ number_format($expenses->sum('amount'),2) }}
                </h2>
                <p class="text-xs text-gray-500">Saved = recorded expense</p>
            </div>

            <div class="overflow-x-auto mt-3">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left border-b">
                            <th class="py-2">Date</th>
                            <th class="py-2">Category</th>
                            <th class="py-2">Description</th>
                            <th class="py-2 text-right">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $row)
                        <tr class="border-b">
                            <td class="py-2">{{ $row->expense_date }}</td>
                            <td class="py-2">{{ $row->category->name ?? '' }}</td>
                            <td class="py-2">{{ $row->description }}</td>
                            <td class="py-2 text-right font-bold text-red-600">
                                ₱{{ number_format($row->amount,2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-gray-400">
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