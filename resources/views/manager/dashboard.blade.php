@extends('layouts.manager')

@section('content')

<style>
    .card {
        background: white;
        padding: 20px;
        border-radius: 10px;
        margin-bottom: 20px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    table th, table td {
        border-bottom: 1px solid #e5e7eb;
        padding: 10px;
        text-align: left;
    }

    button {
        padding: 5px 10px;
        border: none;
        cursor: pointer;
        border-radius: 5px;
    }

    .approve {
        background: #22c55e;
        color: white;
    }

    .reject {
        background: #ef4444;
        color: white;
    }
</style>

<div class="card">
    <h2>📊 Manager Dashboard</h2>
    <p>Welcome, {{ auth()->user()->name }}</p>
</div>

<div class="card">
    <h3>🧾 Pending Approvals</h3>

    <table>
        <tr>
            <th>Product</th>
            <th>Branch</th>
            <th>Qty</th>
            <th>Action</th>
        </tr>

        @foreach($requests as $req)
        <tr>
            <td>{{ $req->product->name }}</td>
            <td>{{ $req->branch->name }}</td>
            <td>{{ $req->quantity }}</td>
            <td>
                <form method="POST" action="/admin/manager/approve/{{ $req->id }}" style="display:inline;">
                    @csrf
                    <button class="approve">Approve</button>
                </form>

                <form method="POST" action="/admin/manager/reject/{{ $req->id }}" style="display:inline;">
                    @csrf
                    <button class="reject">Reject</button>
                </form>
            </td>
        </tr>
        @endforeach
    </table>
</div>

@endsection