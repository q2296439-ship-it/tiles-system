@extends('layouts.manager')

@section('content')

<style>
    table th, table td {
        padding: 12px;
        border-bottom: 1px solid #e5e7eb;
        vertical-align: middle;
    }

    /* CENTER IMPORTANT COLUMNS */
    th:nth-child(3),
    td:nth-child(3),
    th:nth-child(4),
    td:nth-child(4),
    th:nth-child(5),
    td:nth-child(5) {
        text-align: center;
    }

    /* ACTION COLUMN */
    th:last-child,
    td:last-child {
        text-align: center;
    }

    /* ACTION FLEX FIX */
    .action-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 6px;
    }

    /* INPUT STYLE */
    .action-wrapper input {
        height: 30px;
        padding: 5px;
        border: 1px solid #ccc;
        border-radius: 5px;
        width: 100px;
    }

    /* BUTTON STYLE */
    .btn-approve {
        background: #22c55e;
        color: white;
        padding: 5px 8px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    .btn-reject {
        background: #ef4444;
        color: white;
        padding: 5px 8px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }
</style>

<h2 style="margin-bottom:20px;">🧾 Approval Center</h2>

{{-- ===================== --}}
{{-- 🔥 TRANSFER IN --}}
{{-- ===================== --}}
<div style="background:white; padding:20px; border-radius:10px; margin-bottom:20px;">
    <h3 style="margin-bottom:15px;">⬅️ Transfer In Requests</h3>

    <table style="width:100%; border-collapse:collapse;">
        <tr style="background:#f9fafb;">
            <th style="padding:10px;">Product</th>
            <th>From → To</th>
            <th>Qty</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        @forelse($requests->where('type','IN_REQUEST') as $req)
        <tr>
            <td>{{ $req->product->name ?? '-' }}</td>
            <td>{{ $req->from_branch->name ?? '-' }} → {{ $req->branch->name ?? '-' }}</td>
            <td>{{ $req->quantity }}</td>
            <td style="color:orange;">{{ ucfirst($req->status) }}</td>

            <td>
                <div class="action-wrapper">

                    <!-- APPROVE -->
                    <form action="/admin/manager/approve/{{ $req->id }}" method="POST">
                        @csrf
                        <input type="text" name="manager_note" placeholder="Reason..." required>
                        <button class="btn-approve">✔</button>
                    </form>

                    <!-- REJECT -->
                    <form action="/admin/manager/reject/{{ $req->id }}" method="POST">
                        @csrf
                        <input type="text" name="manager_note" placeholder="Reason..." required>
                        <button class="btn-reject">✖</button>
                    </form>

                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" style="text-align:center; padding:15px;">No Transfer In Requests</td>
        </tr>
        @endforelse

    </table>
</div>


{{-- ===================== --}}
{{-- 🔥 TRANSFER OUT --}}
{{-- ===================== --}}
<div style="background:white; padding:20px; border-radius:10px; margin-bottom:20px;">
    <h3 style="margin-bottom:15px;">➡️ Transfer Out Requests</h3>

    <table style="width:100%; border-collapse:collapse;">
        <tr style="background:#f9fafb;">
            <th style="padding:10px;">Product</th>
            <th>From → To</th>
            <th>Qty</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        @forelse($requests->where('type','OUT') as $req)
        <tr>
            <td>{{ $req->product->name ?? '-' }}</td>
            <td>{{ $req->from_branch->name ?? '-' }} → {{ $req->branch->name ?? '-' }}</td>
            <td>{{ $req->quantity }}</td>
            <td style="color:orange;">{{ ucfirst($req->status) }}</td>

            <td>
                <div class="action-wrapper">

                    <form action="/admin/manager/approve/{{ $req->id }}" method="POST">
                        @csrf
                        <input type="text" name="manager_note" placeholder="Reason..." required>
                        <button class="btn-approve">✔</button>
                    </form>

                    <form action="/admin/manager/reject/{{ $req->id }}" method="POST">
                        @csrf
                        <input type="text" name="manager_note" placeholder="Reason..." required>
                        <button class="btn-reject">✖</button>
                    </form>

                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" style="text-align:center; padding:15px;">No Transfer Out Requests</td>
        </tr>
        @endforelse

    </table>
</div>

@endsection