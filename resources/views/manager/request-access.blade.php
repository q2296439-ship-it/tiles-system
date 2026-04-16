@extends('layouts.manager')

@section('content')

<style>
.page{
    max-width:1450px;
    margin:auto;
}

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    flex-wrap:wrap;
    margin-bottom:20px;
}

.title{
    font-size:32px;
    font-weight:800;
    color:#0f172a;
}

.subtitle{
    font-size:14px;
    color:#64748b;
    margin-top:4px;
}

.actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.card,
.stat-card{
    background:#fff;
    border-radius:16px;
    padding:18px;
    box-shadow:0 6px 18px rgba(0,0,0,.05);
}

.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
    margin-bottom:18px;
}

.stat-label{
    font-size:13px;
    color:#64748b;
}

.stat-value{
    font-size:26px;
    font-weight:800;
    margin-top:8px;
    color:#111827;
}

.filters{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-bottom:18px;
}

.input,
.select{
    padding:10px 12px;
    border:1px solid #d1d5db;
    border-radius:10px;
    min-width:180px;
}

.btn{
    padding:10px 14px;
    border:none;
    border-radius:10px;
    cursor:pointer;
    font-weight:700;
    font-size:14px;
    text-decoration:none;
    display:inline-block;
}

.btn-primary{background:#2563eb;color:#fff;}
.btn-success{background:#16a34a;color:#fff;}
.btn-danger{background:#dc2626;color:#fff;}
.btn-dark{background:#111827;color:#fff;}
.btn-light{background:#e5e7eb;color:#111827;}

.table-wrap{
    overflow:auto;
}

.table{
    width:100%;
    border-collapse:collapse;
    min-width:1200px;
}

.table th,
.table td{
    padding:13px;
    border-bottom:1px solid #e5e7eb;
    font-size:14px;
    text-align:left;
    vertical-align:middle;
}

.table th{
    background:#f8fafc;
    font-weight:700;
    color:#334155;
}

.badge{
    padding:6px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:700;
}

.badge-closed{
    background:#fee2e2;
    color:#991b1b;
}

.badge-over{
    background:#dcfce7;
    color:#166534;
}

.badge-short{
    background:#fef3c7;
    color:#92400e;
}

.badge-exact{
    background:#dbeafe;
    color:#1d4ed8;
}

.empty{
    text-align:center;
    padding:40px;
    color:#64748b;
}

.pagination{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    flex-wrap:wrap;
    margin-top:18px;
}

.text-muted{
    color:#64748b;
    font-size:13px;
}
</style>

<div class="page">

    {{-- Header --}}
    <div class="topbar">
        <div>
            <div class="title">🔓 Request Access</div>
            <div class="subtitle">Manage closed deposits and reopen transactions</div>
        </div>

       <div class="actions">
    <a href="{{ route('manager.request.access.excel', request()->query()) }}"
       class="btn btn-success"
       target="_blank">
        Excel
    </a>

    <a href="{{ route('manager.request.access.pdf', request()->query()) }}"
       class="btn btn-dark"
       target="_blank">
        PDF
    </a>
</div>
</div>

    {{-- Stats --}}
    <div class="stats">
        <div class="stat-card">
            <div class="stat-label">Total Records</div>
            <div class="stat-value">{{ $closedDates->total() }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Showing</div>
            <div class="stat-value">{{ $closedDates->count() }}</div>
        </div>

        <div class="stat-card">
            <div class="stat-label">Current Page</div>
            <div class="stat-value">{{ $closedDates->currentPage() }}</div>
        </div>
    </div>

    {{-- Main Card --}}
    <div class="card">

        {{-- Filters --}}
        <form method="GET" class="filters">

            <input type="date"
                   name="date"
                   class="input"
                   value="{{ request('date') }}">

            <select name="branch_id" class="select">
                <option value="">All Branches</option>

                @foreach(\App\Models\Branch::orderBy('name')->get() as $branch)
                    <option value="{{ $branch->id }}"
                        {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn btn-primary">
                Search
            </button>

            <a href="{{ route('manager.request.access') }}" class="btn btn-light">
                Reset
            </a>

        </form>

        {{-- Table --}}
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Branch</th>
                        <th>Date Closed</th>
                        <th>Net</th>
                        <th>Actual</th>
                        <th>Variance</th>
                        <th>Result</th>
                        <th>Closed By</th>
                        <th>Closed At</th>
                        <th>Status</th>
                        <th width="170">Action</th>
                    </tr>
                </thead>

                <tbody>
                @forelse($closedDates as $row)

                    @php
                        $variance = (float) $row->variance;

                        if($variance > 0){
                            $varianceText = 'Over';
                            $varianceClass = 'badge-over';
                        }elseif($variance < 0){
                            $varianceText = 'Short';
                            $varianceClass = 'badge-short';
                        }else{
                            $varianceText = 'Exact';
                            $varianceClass = 'badge-exact';
                        }
                    @endphp

                    <tr>
                        <td>{{ $loop->iteration + (($closedDates->currentPage() - 1) * $closedDates->perPage()) }}</td>

                        <td>{{ $row->branch_name ?? 'N/A' }}</td>

                        <td>{{ $row->deposit_date }}</td>

                        <td>₱{{ number_format($row->net_amount, 2) }}</td>

                        <td>₱{{ number_format($row->actual_amount, 2) }}</td>

                        <td>₱{{ number_format($row->variance, 2) }}</td>

                        <td>
                            <span class="badge {{ $varianceClass }}">
                                {{ $varianceText }}
                            </span>
                        </td>

                        <td>
                            {{ $row->user->name ?? 'N/A' }}
                        </td>

                        <td>
                            {{ \Carbon\Carbon::parse($row->created_at)->format('M d, Y h:i A') }}
                        </td>

                        <td>
                            <span class="badge badge-closed">Closed</span>
                        </td>

                        <td>
                            @if(auth()->user()->role != 'audit')
                                <form method="POST" action="{{ route('manager.request.open') }}">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $row->id }}">

                                    <button type="submit"
                                            class="btn btn-danger"
                                            onclick="return confirm('Reopen this transaction?')">
                                        Reopen
                                    </button>
                                </form>
                            @else
                                <span class="text-muted">View Only</span>
                            @endif
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="11" class="empty">
                            No closed transactions found.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer Pagination --}}
        <div class="pagination">

            <div class="text-muted">
                Showing
                {{ $closedDates->firstItem() ?? 0 }}
                -
                {{ $closedDates->lastItem() ?? 0 }}
                of
                {{ $closedDates->total() }}
            </div>

            <div style="display:flex;gap:8px;">

                @if($closedDates->onFirstPage())
                    <button class="btn btn-light" disabled>Previous</button>
                @else
                    <a href="{{ $closedDates->previousPageUrl() }}" class="btn btn-primary">
                        Previous
                    </a>
                @endif

                @if($closedDates->hasMorePages())
                    <a href="{{ $closedDates->nextPageUrl() }}" class="btn btn-primary">
                        Next
                    </a>
                @else
                    <button class="btn btn-light" disabled>Next</button>
                @endif

            </div>

        </div>

    </div>

</div>

@endsection