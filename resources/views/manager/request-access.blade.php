@extends('layouts.manager')

@section('content')

<style>
.page{
    max-width:1200px;
    margin:auto;
}

.title{
    font-size:28px;
    font-weight:700;
    margin-bottom:20px;
    color:#0f172a;
}

.card{
    background:#fff;
    border-radius:14px;
    padding:22px;
    box-shadow:0 6px 18px rgba(0,0,0,0.06);
}

.alert-success{
    background:#dcfce7;
    color:#166534;
    padding:12px;
    border-radius:10px;
    margin-bottom:15px;
}

.table{
    width:100%;
    border-collapse:collapse;
}

.table th,
.table td{
    padding:12px;
    border-bottom:1px solid #e5e7eb;
    text-align:left;
    font-size:14px;
}

.table th{
    background:#f8fafc;
    color:#334155;
}

.badge{
    padding:6px 10px;
    border-radius:20px;
    font-size:12px;
    font-weight:600;
    background:#fef3c7;
    color:#92400e;
}

.btn{
    background:#2563eb;
    color:#fff;
    border:none;
    padding:8px 14px;
    border-radius:8px;
    cursor:pointer;
    font-size:13px;
}

.empty{
    text-align:center;
    padding:30px;
    color:#64748b;
}
</style>

<div class="page">

    <div class="title">🔓 Request Access</div>

    @if(session('success'))
        <div class="alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">

        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Branch</th>
                    <th>Date Closed</th>
                    <th>Actual Deposit</th>
                    <th>Status</th>
                    <th width="180">Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($closedDates as $row)
                    <tr>
                        <td>{{ $loop->iteration }}</td>

                        <td>
                            {{ $row->branch->name ?? 'N/A' }}
                        </td>

                        <td>
                            {{ $row->deposit_date }}
                        </td>

                        <td>
                            ₱{{ number_format($row->actual_amount, 2) }}
                        </td>

                        <td>
                            <span class="badge">Closed</span>
                        </td>

                        <td>
                            <form method="POST" action="{{ route('manager.request.open') }}">
                                @csrf
                                <input type="hidden" name="id" value="{{ $row->id }}">

                                <button type="submit" class="btn"
                                    onclick="return confirm('Open transaction for this date?')">
                                    Open Transaction
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="empty">
                            No closed transactions found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div style="margin-top:15px;">
            {{ $closedDates->links() }}
        </div>

    </div>

</div>

@endsection