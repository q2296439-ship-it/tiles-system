@extends('layouts.cashier')

@section('title', 'A/R Accounts')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold text-dark mb-1">📒 A/R Accounts</h2>
            <p class="text-muted mb-0">Manage DP / Partial customer balances</p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-4 mb-4">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <small class="text-muted text-uppercase fw-semibold">
                        Pending Accounts
                    </small>
                    <h2 class="fw-bold text-primary mt-2 mb-0">
                        {{ $rows->total() }}
                    </h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <small class="text-muted text-uppercase fw-semibold">
                        Total Receivable
                    </small>
                    <h2 class="fw-bold text-success mt-2 mb-0">
                        ₱{{ number_format($rows->sum('balance'), 2) }}
                    </h2>
                </div>
            </div>
        </div>

    </div>

    {{-- Success Alert --}}
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4 mb-4">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">

            <form method="GET" action="{{ route('cashier.ar.accounts') }}">
                <div class="row g-3 align-items-end">

                    <div class="col-md-5">
                        <label class="form-label fw-semibold mb-2">Search</label>
                        <input type="text"
                               name="search"
                               class="form-control rounded-3"
                               placeholder="Receipt no / Customer name"
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold mb-2">Date</label>
                        <input type="date"
                               name="date"
                               class="form-control rounded-3"
                               value="{{ request('date') }}">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100 rounded-3">
                            Filter
                        </button>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('cashier.ar.accounts') }}"
                           class="btn btn-light border w-100 rounded-3">
                            Reset
                        </a>
                    </div>

                </div>
            </form>

        </div>
    </div>

    {{-- Table Card --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th class="px-4 py-3">#</th>
                            <th class="py-3">Receipt No</th>
                            <th class="py-3">Date</th>
                            <th class="py-3">Customer</th>
                            <th class="py-3">Total</th>
                            <th class="py-3">Paid</th>
                            <th class="py-3">Balance</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="px-4">{{ $loop->iteration }}</td>

                            <td class="fw-semibold text-dark">
                                {{ $row->receipt_no }}
                            </td>

                            <td>
                                {{ date('M d, Y', strtotime($row->receipt_date)) }}
                            </td>

                            <td>{{ $row->customer_name }}</td>

                            <td>
                                ₱{{ number_format($row->total_amount, 2) }}
                            </td>

                            <td class="text-success fw-semibold">
                                ₱{{ number_format($row->paid_amount, 2) }}
                            </td>

                            <td class="text-danger fw-bold">
                                ₱{{ number_format($row->balance, 2) }}
                            </td>

                            <td>
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill">
                                    Pending
                                </span>
                            </td>

                            <td class="text-center">
                                <button class="btn btn-success btn-sm px-3 rounded-3"
                                        data-bs-toggle="modal"
                                        data-bs-target="#payModal{{ $row->id }}">
                                    💵 Payment
                                </button>
                            </td>
                        </tr>

                        {{-- Payment Modal --}}
                        <div class="modal fade" id="payModal{{ $row->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <form method="POST"
                                      action="{{ route('cashier.ar.payment', $row->id) }}">
                                    @csrf

                                    <div class="modal-content border-0 rounded-4 shadow">

                                        <div class="modal-header border-0 pb-0 px-4 pt-4">
                                            <h5 class="modal-title fw-bold">
                                                Payment - {{ $row->receipt_no }}
                                            </h5>

                                            <button type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body px-4 pt-3">

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">
                                                    Remaining Balance
                                                </label>
                                                <input type="text"
                                                       class="form-control rounded-3"
                                                       value="₱{{ number_format($row->balance, 2) }}"
                                                       readonly>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">
                                                    Payment Amount
                                                </label>
                                                <input type="number"
                                                       step="0.01"
                                                       max="{{ $row->balance }}"
                                                       name="payment"
                                                       class="form-control rounded-3"
                                                       required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">
                                                    Payment Date
                                                </label>
                                                <input type="date"
                                                       name="payment_date"
                                                       class="form-control rounded-3"
                                                       value="{{ date('Y-m-d') }}">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">
                                                    Notes
                                                </label>
                                                <textarea name="notes"
                                                          rows="2"
                                                          class="form-control rounded-3"></textarea>
                                            </div>

                                        </div>

                                        <div class="modal-footer border-0 px-4 pb-4">
                                            <button class="btn btn-success w-100 rounded-3 py-2">
                                                Save Payment
                                            </button>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted fs-6">
                                No pending accounts found.
                            </td>
                        </tr>
                    @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

    {{-- Pagination --}}
    @if($rows->hasPages())
        <div class="mt-4">
            {{ $rows->links() }}
        </div>
    @endif

</div>
@endsection