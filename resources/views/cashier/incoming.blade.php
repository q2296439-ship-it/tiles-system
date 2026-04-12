@extends('layouts.cashier')

@section('content')

<style>
    .table-wrapper {
        background: white;
        padding: 20px;
        border-radius: 12px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: #f1f5f9;
        font-size: 13px;
        color: #64748b;
        text-align: left;
    }

    th, td {
        padding: 12px 10px;
        border-bottom: 1px solid #e5e7eb;
        font-size: 14px;
    }

    /* ALIGNMENT */
    th:nth-child(3),
    td:nth-child(3),
    th:nth-child(4),
    td:nth-child(4),
    th:nth-child(5),
    td:nth-child(5) {
        text-align: center;
    }

    /* STATUS */
    .status-ready {
        color: #16a34a;
        font-weight: 500;
    }

    /* BUTTON */
    .btn-receive {
        background: #16a34a;
        color: white;
        padding: 6px 12px;
        border-radius: 6px;
        border: none;
        cursor: pointer;
        font-size: 13px;
    }

    h2 {
        font-size: 20px;
        margin-bottom: 15px;
    }
</style>

<h2>📦 Incoming Transfer Stock</h2>

<div class="table-wrapper">

    <table>
        <tr>
            <th style="padding:10px;">Product</th>
            <th>From Branch</th>
            <th>Qty</th>
            <th>Status</th>
            <th>Action</th>
        </tr>

        @forelse($requests as $req)
        <tr>
            <td>{{ $req->product->name ?? '-' }}</td>
            <td>{{ $req->from_branch->name ?? '-' }}</td>
            <td>{{ $req->quantity }}</td>

            <td class="status-ready">
                Ready to Receive
            </td>

            <td>
                <form action="/cashier/receive/{{ $req->id }}" method="POST">
                    @csrf
                    <button class="btn-receive">
                        ✔ Receive
                    </button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" style="text-align:center; padding:15px;">
                No incoming transfers
            </td>
        </tr>
        @endforelse

    </table>

</div>

@endsection