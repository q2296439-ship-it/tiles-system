@extends('layouts.cashier')

@section('content')

<div class="w-full flex justify-center px-4 py-6">
    <div class="w-full max-w-5xl bg-blue-100 border border-blue-300 rounded-2xl shadow p-6">

        <div class="text-center mb-6">
            <h1 class="text-4xl font-bold text-gray-800">STORE EXPENSES</h1>
            <p class="text-sm text-gray-600">Track daily expenses and monitor store cash outflow</p>
        </div>

        @if(session('success'))
            <div id="successAlert" class="mb-4 bg-green-500 text-white px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('cashier.expenses.store') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-3 mb-3">

                <input type="date"
                       name="expense_date"
                       value="{{ date('Y-m-d') }}"
                       class="border rounded-lg px-3 py-2 w-full"
                       required>

                <select name="category_id"
                        class="border rounded-lg px-3 py-2 w-full"
                        required>
                    <option value="">Select Category</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>

                <input type="number"
                       step="0.01"
                       name="amount"
                       placeholder="₱ Amount"
                       class="border rounded-lg px-3 py-2 w-full"
                       required>

                <select name="payment_method"
                        class="border rounded-lg px-3 py-2 w-full">
                    <option value="cash">Cash</option>
                    <option value="bank">Bank</option>
                    <option value="gcash">GCash</option>
                </select>
            </div>

            <div class="mb-4">
                <textarea name="description"
                          rows="4"
                          class="border rounded-lg px-3 py-2 w-full"
                          placeholder="Notes / Expense Description"></textarea>
            </div>

            <div class="flex flex-wrap gap-3 mb-5">
                <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white px-5 py-2 rounded-lg font-semibold">
                    💾 Save Expense
                </button>

                <a href="{{ route('cashier.expenses') }}"
                   class="bg-red-500 hover:bg-red-600 text-white px-5 py-2 rounded-lg font-semibold">
                    ✖ Clear
                </a>
            </div>
        </form>

        <div class="bg-white rounded-xl border p-4">
            <div class="mb-3">
                <p class="text-sm text-gray-600">Expense Records</p>
                <h2 class="text-3xl font-bold text-red-600">
                    ₱{{ number_format($expenses->sum('amount'), 2) }}
                </h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="text-left px-3 py-2">Date</th>
                            <th class="text-left px-3 py-2">Category</th>
                            <th class="text-left px-3 py-2">Description</th>
                            <th class="text-right px-3 py-2">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($expenses as $row)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-3 py-2">{{ $row->expense_date }}</td>
                            <td class="px-3 py-2">{{ $row->category->name ?? '' }}</td>
                            <td class="px-3 py-2">{{ $row->description }}</td>
                            <td class="px-3 py-2 text-right font-semibold text-red-600">
                                ₱{{ number_format($row->amount, 2) }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-gray-400">
                                No expense records yet.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

@if(session('success'))
<script>
    setTimeout(() => {
        const alertBox = document.getElementById('successAlert');
        if (alertBox) alertBox.style.display = 'none';
    }, 2500);
</script>
@endif

@endsection