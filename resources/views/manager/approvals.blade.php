@extends('layouts.admin')

@section('content')

<div class="card">
    <h2>🧾 Pending Approvals</h2>

    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

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
                    <button>✅ Approve</button>
                </form>

                <form method="POST" action="/admin/manager/reject/{{ $req->id }}" style="display:inline;">
                    @csrf
                    <button>❌ Reject</button>
                </form>
            </td>
        </tr>
        @endforeach
    </table>
</div>

@endsection