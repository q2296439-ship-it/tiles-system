@extends('layouts.cashier')

@section('title', 'A/R Accounts')

@section('content')
<div class="container-fluid px-3 py-3">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">📒 A/R Accounts</h4>
            <small class="text-muted">Pending DP / Partial Payments</small>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <small class="text-muted">Total Pending Accounts</small>
                    <h3 class="fw-bold text-primary mb-0">{{ $rows->count() ?? 0 }}</h3>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body">
                    <small class="text-muted">Total Receivable</small>
                    <h3 class="fw-bold text-success mb-0">
                        ₱{{ number_format($rows->sum('balance') ?? 0, 2) }}
                    </h3>
                </div>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('cashier.ar.accounts') }}">
                <div class="row g-2">
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control"
                               placeholder="Search customer / receipt..."
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <input type="date" name="date" class="form-control"
                               value="{{ request('date') }}">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">Filter</button>
                    </div>

                    <div class="col-md-2">
                        <a href="{{ route('cashier.ar.accounts') }}"
                           class="btn btn-secondary w-100">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Table --}}
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Receipt No</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Paid</th>
                            <th>Balance</th>
                            <th>Status</th>
                            <th width="120">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $row->receipt_no }}</td>
                            <td>{{ $row->receipt_date }}</td>
                            <td>{{ $row->customer_name }}</td>
                            <td>₱{{ number_format($row->total_amount, 2) }}</td>
                            <td>₱{{ number_format($row->paid_amount, 2) }}</td>
                            <td class="fw-bold text-danger">
                                ₱{{ number_format($row->balance, 2) }}
                            </td>
                            <td>
                                <span class="badge bg-warning text-dark">
                                    {{ ucfirst($row->status) }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-success btn-sm w-100"
                                        data-bs-toggle="modal"
                                        data-bs-target="#payModal{{ $row->id }}">
                                    💵 Payment
                                </button>
                            </td>
                        </tr>

                        {{-- Payment Modal --}}
                        <div class="modal fade" id="payModal{{ $row->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <form method="POST"
                                      action="#">
                                    @csrf

                                    <div class="modal-content rounded-4 border-0">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                Payment - {{ $row->receipt_no }}
                                            </h5>
                                            <button type="button" class="btn-close"
                                                    data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">

                                            <div class="mb-2">
                                                <label class="form-label">Remaining Balance</label>
                                                <input type="text"
                                                       class="form-control"
                                                       value="₱{{ number_format($row->balance,2) }}"
                                                       readonly>
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label">Payment Amount</label>
                                                <input type="number"
                                                       step="0.01"
                                                       name="payment"
                                                       class="form-control"
                                                       required>
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label">Payment Date</label>
                                                <input type="date"
                                                       name="payment_date"
                                                       class="form-control"
                                                       value="{{ date('Y-m-d') }}">
                                            </div>

                                            <div class="mb-2">
                                                <label class="form-label">Notes</label>
                                                <textarea name="notes"
                                                          class="form-control"
                                                          rows="2"></textarea>
                                            </div>

                                        </div>

                                        <div class="modal-footer">
                                            <button class="btn btn-success w-100">
                                                Save Payment
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">
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
    <div class="mt-3">
        {{ $rows->links() }}
    </div>

</div>
@endsection