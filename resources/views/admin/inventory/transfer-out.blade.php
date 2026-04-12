@extends('layouts.admin')

@section('content')

<style>
.container { max-width: 1100px; margin:auto; }

.card {
    background:white;
    padding:20px;
    border-radius:12px;
    box-shadow:0 4px 10px rgba(0,0,0,0.05);
}

h2 { margin-bottom:20px; }

/* TABLE */
.table {
    width:100%;
    border-collapse:collapse;
}

.table th {
    background:#f9fafb;
    text-align:left;
}

.table th, .table td {
    padding:12px;
    border-bottom:1px solid #eee;
}

/* STATUS */
.badge {
    padding:5px 10px;
    border-radius:6px;
    font-size:12px;
    color:white;
}

.pending { background:#f59e0b; }
.approved { background:#16a34a; }
.rejected { background:#dc2626; }
.completed { background:#3b82f6; }

.table tr:hover {
    background:#f9fafb;
}
</style>

<div class="container">

<div class="card">

<h2>📤 Transfer Out History</h2>

<table class="table">
<thead>
<tr>
    <th>Product</th>
    <th>From Branch</th>
    <th>To Branch</th>
    <th>Quantity</th>
    <th>Status</th>
    <th>Date</th>
</tr>
</thead>

<tbody>

@forelse($transfers as $t)
<tr>
    <td>{{ $t->product->name ?? '-' }}</td>
    <td>{{ $t->from_branch_name ?? 'N/A' }}</td>
    <td>{{ $t->to_branch_name ?? 'N/A' }}</td>
    <td>{{ $t->quantity }}</td>

    <td>
        <span class="badge {{ $t->status }}">
            {{ ucfirst($t->status) }}
        </span>
    </td>

    <td>{{ $t->created_at->format('Y-m-d') }}</td>
</tr>
@empty
<tr>
    <td colspan="6">No transfer records found.</td>
</tr>
@endforelse

</tbody>
</table>

</div>

</div>

@endsection