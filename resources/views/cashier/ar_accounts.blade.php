@extends('layouts.cashier')

@section('title', 'A/R Accounts')

@section('content')
<div class="container-fluid px-4 py-4">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h2 class="fw-bold mb-1 text-dark">📒 A/R Accounts</h2>
            <p class="text-muted mb-0">Manage DP / Partial customer balances</p>
        </div>
    </div>

    {{-- Summary --}}
    <div class="row g-3 mb-4">

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Pending Accounts</small>
                    <h2 class="fw-bold text-primary mb-0">
                        {{ $rows->total() }}
                    </h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <small class="text-muted d-block mb-1">Total Receivable</small>
                    <h2 class="fw-bold text-success mb-0">
                        ₱{{ number_format($rows->sum('balance'), 2) }}
                    </h2>
                </div>
            </div>
        </div>

    </div>

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success rounded-4 shadow-sm border-0">
            {{ session('success') }}
        </div>
    @endif

    {{-- Filter Card --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body">

            <form method="GET" action="{{ route('cashier.ar.accounts') }}">
                <div class="row g-2 align-items-end">

                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Search</label>
                        <input type="text"
                               name="search"
                               class="form-control rounded-3"
                               placeholder="Receipt no / Customer name"
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Date</label>
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

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light">
                        <tr>
                            <th class="px-3">#</th>
                            <th>Receipt No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th class="text-center">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td class="px-3">{{ $loop->iteration }}</td>
                            <td class="fw-semibold">{{ $row->receipt_no }}</td>
                            <td>{{ date('M d, Y', strtotime($row->receipt_date)) }}</td>
                            <td>{{ $row->customer_name }}</td>
                            <td>₱{{ number_format($row->total_amount, 2) }}</td>
                            <td class="text-success">
                                ₱{{ number_format($row->paid_amount, 2) }}
                            </td>
                            <td class="fw-bold text-danger">
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

                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold">
                                                Payment - {{ $row->receipt_no }}
                                            </h5>
                                            <button type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">

                                            <div class="mb-3">
                                                <label class="form-label">Remaining Balance</label>
                                                <input type="text"
                                                       class="form-control rounded-3"
                                                       value="₱{{ number_format($row->balance, 2) }}"
                                                       readonly>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Payment Amount</label>
                                                <input type="number"
                                                       name="payment"
                                                       step="0.01"
                                                       max="{{ $row->balance }}"
                                                       class="form-control rounded-3"
                                                       required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Payment Date</label>
                                                <input type="date"
                                                       name="payment_date"
                                                       class="form-control rounded-3"
                                                       value="{{ date('Y-m-d') }}">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Notes</label>
                                                <textarea name="notes"
                                                          class="form-control rounded-3"
                                                          rows="2"></textarea>
                                            </div>

                                        </div>

                                        <div class="modal-footer border-0 pt-0">
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
                            <td colspan="9" class="text-center py-5 text-muted">
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
    <div class="mt-4">
        {{ $rows->links() }}
    </div>

</div>
@endsection