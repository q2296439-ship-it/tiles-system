@extends('layouts.admin')

@section('content')
<div class="p-6">

    <div class="bg-white rounded-xl shadow-sm border p-6 mb-6">
        <h2 class="text-2xl font-bold text-gray-800 mb-1">Store Expenses</h2>
        <p class="text-sm text-gray-500 mb-6">Record daily store expenses and monitor cash outflow.</p>

        @if(session('success'))
            <div class="mb-4 px-4 py-3 rounded-lg bg-green-100 text-green-700">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('cashier.expenses.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                <div>
                    <label class="block text-sm font-medium mb-1">Date</label>
                    <input type="date" name="expense_date"
                        class="w-full border rounded-lg px-3 py-2" required>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Category</label>
                    <select name="category_id"
                        class="w-full border rounded-lg px-3 py-2" required>
                        <option value="">Select Category</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Amount</label>
                    <input type="number" step="0.01" name="amount"
                        class="w-full border rounded-lg px-3 py-2"
                        placeholder="0.00" required>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">Description</label>
                    <input type="text" name="description"
                        class="w-full border rounded-lg px-3 py-2"
                        placeholder="Enter expense details">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Payment Method</label>
                    <select name="payment_method"
                        class="w-full border rounded-lg px-3 py-2">
                        <option value="cash">Cash</option>
                        <option value="bank">Bank</option>
                        <option value="gcash">GCash</option>
                    </select>
                </div>
            </div>

            <div class="mt-5">
                <button type="submit"
                    class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-lg">
                    Save Expense
                </button>
            </div>
        </form>
    </div>


    <div class="bg-white rounded-xl shadow-sm border p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Expense History</h3>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-700">
                        <th class="text-left px-4 py-3">Date</th>
                        <th class="text-left px-4 py-3">Category</th>
                        <th class="text-left px-4 py-3">Description</th>
                        <th class="text-right px-4 py-3">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $row)
                    <tr class="border-b hover:bg-gray-50">
                        <td class="px-4 py-3">{{ $row->expense_date }}</td>
                        <td class="px-4 py-3">{{ $row->category->name ?? '' }}</td>
                        <td class="px-4 py-3">{{ $row->description }}</td>
                        <td class="px-4 py-3 text-right font-medium">
                            {{ number_format($row->amount,2) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center text-gray-400 py-6">
                            No expense records found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection