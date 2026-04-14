@extends('layouts.admin')

@section('content')

<style>
.container{
    max-width:1300px;
    margin:auto;
    font-family:'Segoe UI',Tahoma,sans-serif;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    margin-bottom:25px;
    flex-wrap:wrap;
}

.header h2{
    margin:0;
    font-size:24px;
    font-weight:700;
    color:#111827;
}

.filters{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
}

.search,.select{
    padding:11px 14px;
    border:1px solid #d1d5db;
    border-radius:10px;
    font-size:14px;
    min-width:200px;
    outline:none;
    background:#fff;
}

.search:focus,.select:focus{
    border-color:#3b82f6;
    box-shadow:0 0 0 3px rgba(59,130,246,.10);
}

.btn{
    background:#3b82f6;
    color:#fff;
    border:none;
    padding:11px 16px;
    border-radius:10px;
    cursor:pointer;
    font-size:14px;
    font-weight:600;
    text-decoration:none;
    display:inline-block;
    transition:0.2s;
}

.btn:hover{
    background:#2563eb;
}

.btn-success{
    background:#16a34a;
}

.btn-success:hover{
    background:#15803d;
}

.btn-danger{
    background:#dc2626;
}

.btn-danger:hover{
    background:#b91c1c;
}

.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
    margin-bottom:25px;
}

.stat{
    background:#fff;
    padding:22px;
    border-radius:14px;
    box-shadow:0 6px 18px rgba(0,0,0,0.05);
}

.stat small{
    color:#6b7280;
    font-size:13px;
}

.stat h2{
    margin:8px 0 0;
    font-size:24px;
    font-weight:700;
    color:#111827;
}

.card{
    background:#fff;
    padding:22px;
    border-radius:14px;
    box-shadow:0 6px 18px rgba(0,0,0,0.06);
    overflow-x:auto;
}

.table-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
    gap:10px;
    flex-wrap:wrap;
}

.table{
    width:100%;
    border-collapse:collapse;
}

.table th{
    background:#f9fafb;
    text-align:left;
    font-size:13px;
    font-weight:700;
    color:#374151;
    white-space:nowrap;
}

.table th,
.table td{
    padding:14px;
    border-bottom:1px solid #e5e7eb;
    font-size:14px;
}

.table tbody tr:hover{
    background:#f9fafb;
}

.badge{
    padding:6px 10px;
    border-radius:999px;
    font-size:11px;
    font-weight:700;
    color:#fff;
    display:inline-block;
}

.pending{ background:#f59e0b; }
.approved{ background:#16a34a; }
.rejected{ background:#dc2626; }
.completed{ background:#3b82f6; }

.empty{
    text-align:center;
    padding:20px;
    color:#6b7280;
}

.pagination{
    margin-top:18px;
    display:flex;
    justify-content:center;
    align-items:center;
    flex-wrap:wrap;
    gap:6px;
}

/* FIX LARGE PAGINATION ARROWS */
.pagination svg{
    width:18px !important;
    height:18px !important;
}

svg{
    max-width:18px;
    max-height:18px;
}
</style>

<div class="container">

    <div class="header">
        <h2>📤 Transfer Out History</h2>

        <form method="GET" class="filters">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="🔍 Search product..."
                class="search">

            <select name="status" class="select">
                <option value="">All Status</option>
                <option value="pending" {{ request('status')=='pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status')=='approved' ? 'selected' : '' }}>Approved</option>
                <option value="completed" {{ request('status')=='completed' ? 'selected' : '' }}>Completed</option>
                <option value="rejected" {{ request('status')=='rejected' ? 'selected' : '' }}>Rejected</option>
            </select>

            <button type="submit" class="btn">Filter</button>

            <a href="{{ url()->current() }}?export=excel&search={{ request('search') }}&status={{ request('status') }}"
               class="btn btn-success">
               📊 Excel
            </a>

            <a href="{{ url()->current() }}?export=pdf&search={{ request('search') }}&status={{ request('status') }}"
               target="_blank"
               class="btn btn-danger">
               🧾 PDF
            </a>

        </form>
    </div>

    {{-- STATS --}}
    <div class="stats">

        <div class="stat">
            <small>Total Records</small>
            <h2>{{ $transfers->count() }}</h2>
        </div>

        <div class="stat">
            <small>Pending</small>
            <h2>{{ $transfers->where('status','pending')->count() }}</h2>
        </div>

        <div class="stat">
            <small>Completed</small>
            <h2>{{ $transfers->where('status','completed')->count() }}</h2>
        </div>

        <div class="stat">
            <small>Rejected</small>
            <h2>{{ $transfers->where('status','rejected')->count() }}</h2>
        </div>

    </div>

    {{-- TABLE --}}
    <div class="card">

        <div class="table-top">
            <strong>Outgoing Transfer Records</strong>
            <small>{{ $transfers->count() }} row(s)</small>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>From Branch</th>
                    <th>To Branch</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Requested By</th>
                    <th>Approved By</th>
                    <th>Date</th>
                </tr>
            </thead>

            <tbody>
            @forelse($transfers as $t)
                <tr>
                    <td><strong>{{ $t->product->name ?? '-' }}</strong></td>
                    <td>{{ $t->from_branch->name ?? 'N/A' }}</td>
                    <td>{{ $t->branch->name ?? 'N/A' }}</td>
                    <td>{{ $t->quantity }}</td>

                    <td>
                        <span class="badge {{ $t->status }}">
                            {{ ucfirst($t->status) }}
                        </span>
                    </td>

                    <td>{{ $t->requester->name ?? 'N/A' }}</td>
                    <td>{{ $t->approver->name ?? 'N/A' }}</td>
                    <td>{{ $t->created_at->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty">No transfer records found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="pagination">
            @if(method_exists($transfers,'links'))
                {{ $transfers->links() }}
            @endif
        </div>

    </div>

</div>

@endsection