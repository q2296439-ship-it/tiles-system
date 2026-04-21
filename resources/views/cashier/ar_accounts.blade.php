@php
$layout = match(strtolower(auth()->user()->role)) {
    'admin'   => 'layouts.admin',
    'manager' => 'layouts.manager',
    'audit'   => 'layouts.manager',
    default   => 'layouts.cashier',
};

$routePrefix = match(strtolower(auth()->user()->role)) {
    'admin'   => 'admin',
    'manager' => 'manager',
    'audit'   => 'manager',
    default   => 'cashier',
};
@endphp

@extends($layout)

@section('title', 'A/R Accounts')

@section('content')
<div class="container-fluid px-4 py-4">

<div class="card border-0 shadow-sm rounded-4">
<div class="card-body p-4">

<div class="mb-4">
    <h2 class="fw-bold text-dark mb-1">📒 A/R Accounts</h2>
    <p class="text-muted mb-0">
        Pending DP / Partial customer balances
    </p>
</div>

<form method="GET" action="/{{ $routePrefix }}/ar-accounts">
<div class="row g-2 mb-4 align-items-end">

<div class="col-md-2">
    <input type="date"
           name="date"
           class="form-control rounded-3"
           value="{{ request('date') }}">
</div>

<div class="col-md-3">
    <input type="text"
           name="search"
           class="form-control rounded-3"
           placeholder="Search customer / receipt"
           value="{{ request('search') }}">
</div>

@if($role !== 'cashier')
<div class="col-md-3">
    <select name="branch_id"
            class="form-select rounded-3">
        <option value="">All Branches</option>

        @foreach($branches as $branch)
            <option value="{{ $branch->id }}"
                {{ $selectedBranch == $branch->id ? 'selected' : '' }}>
                {{ $branch->name }}
            </option>
        @endforeach
    </select>
</div>
@endif

<div class="col-md-2">
    <button class="btn btn-primary w-100 rounded-3 fw-semibold">
        Filter
    </button>
</div>

<div class="col-md-2">
    <a href="/{{ $routePrefix }}/ar-accounts"
       class="btn btn-secondary w-100 rounded-3 fw-semibold">
        Reset
    </a>
</div>

</div>
</form>

<div class="row g-3 mb-4">

<div class="col-md-6">
<div class="border rounded-4 p-3 bg-light">
    <small class="text-muted d-block">Pending Accounts</small>
    <h2 class="fw-bold mb-0 text-primary">
        {{ $rows->total() }}
    </h2>
</div>
</div>

<div class="col-md-6">
<div class="border rounded-4 p-3 bg-light">
    <small class="text-muted d-block">Total Receivable</small>
    <h2 class="fw-bold mb-0 text-success">
        ₱{{ number_format($rows->sum('balance'), 2) }}
    </h2>
</div>
</div>

</div>

@if(session('success'))
<div class="alert alert-success rounded-3 border-0 shadow-sm">
    {{ session('success') }}
</div>
@endif

@if(session('error'))
<div class="alert alert-danger rounded-3 border-0 shadow-sm">
    {{ session('error') }}
</div>
@endif

<div class="table-responsive">
<table class="table align-middle table-hover">

<thead class="table-light">
<tr>
<th class="text-center">#</th>
<th class="text-center">Receipt No</th>
<th class="text-center">Date</th>
<th class="text-center">Customer</th>
<th class="text-center">Total</th>
<th class="text-center">Paid</th>
<th class="text-center">Balance</th>
<th class="text-center">Status</th>
<th class="text-center">Action</th>
</tr>
</thead>

<tbody>
@forelse($rows as $row)
<tr>
<td class="text-center">
{{ $loop->iteration + (($rows->currentPage()-1)*$rows->perPage()) }}
</td>

<td class="fw-semibold text-center">
{{ $row->receipt_no }}
</td>

<td class="text-center">
{{ date('Y-m-d', strtotime($row->receipt_date)) }}
</td>

<td class="text-center">
{{ $row->customer_name }}
</td>

<td class="text-center">
₱{{ number_format($row->total_amount, 2) }}
</td>

<td class="text-center text-success fw-semibold">
₱{{ number_format($row->paid_amount, 2) }}
</td>

<td class="text-center text-danger fw-bold">
₱{{ number_format($row->balance, 2) }}
</td>

<td class="text-center">
<span class="badge bg-warning text-dark rounded-pill px-3 py-2">
Pending
</span>
</td>

<td class="text-center">
<button class="btn btn-success btn-sm rounded-3 px-3"
        data-bs-toggle="modal"
        data-bs-target="#payModal{{ $row->id }}">
💵 Payment
</button>
</td>
</tr>

<div class="modal fade" id="payModal{{ $row->id }}" tabindex="-1">
<div class="modal-dialog modal-dialog-centered">

<form method="POST"
      action="/{{ $routePrefix }}/ar-payment/{{ $row->id }}">
@csrf

<div class="modal-content border-0 rounded-4 shadow">

<div class="modal-header">
<h5 class="modal-title fw-bold">
Payment - {{ $row->receipt_no }}
</h5>

<button type="button"
        class="btn-close"
        data-bs-dismiss="modal"></button>
</div>

<div class="modal-body">

<div class="mb-3">
<label class="form-label">Balance</label>
<input type="text"
       class="form-control"
       value="₱{{ number_format($row->balance, 2) }}"
       readonly>
</div>

<div class="mb-3">
<label class="form-label">Payment</label>
<input type="number"
       name="payment"
       step="0.01"
       max="{{ $row->balance }}"
       class="form-control"
       required>
</div>

<div class="mb-3">
<label class="form-label">Date</label>
<input type="date"
       name="payment_date"
       class="form-control"
       value="{{ date('Y-m-d') }}">
</div>

<div class="mb-3">
<label class="form-label">Notes</label>
<textarea name="notes"
          rows="2"
          class="form-control"></textarea>
</div>

</div>

<div class="modal-footer">
<button class="btn btn-success w-100 rounded-3">
Save Payment
</button>
</div>

</div>
</form>

</div>
</div>

@empty
<tr>
<td colspan="9" class="text-center text-muted py-5">
No pending accounts found.
</td>
</tr>
@endforelse
</tbody>

</table>
</div>

<div class="d-flex justify-content-between mt-4">
{{ $rows->links() }}
</div>

</div>
</div>

</div>
@endsection