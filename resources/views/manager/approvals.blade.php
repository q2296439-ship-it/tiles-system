@extends('layouts.manager')

@section('content')

<h2 style="margin-bottom:20px;">🧾 All Pending Approvals</h2>

<div style="background:white; padding:20px; border-radius:10px;">

    <table style="width:100%; border-collapse:collapse;">
        <tr style="background:#f1f5f9;">
            <th style="padding:10px;">Type</th>
            <th>Product</th>
            <th>From → To</th>
            <th>Qty</th>
            <th>Status</th>
            <th>Action</th> <!-- ✅ ADDED -->
        </tr>

        @forelse($requests as $req)
        <tr>
            <td style="padding:10px;">{{ $req->type }}</td>
            <td>{{ $req->product->name ?? '-' }}</td>
            <td>
                {{ $req->from_branch->name ?? '-' }} →
                {{ $req->branch->name ?? '-' }}
            </td>
            <td>{{ $req->quantity }}</td>
            <td style="color:orange;">
                {{ ucfirst($req->status) }}
            </td>

            <!-- ✅ ADDED -->
            <td>
                <!-- APPROVE -->
                <form action="/admin/manager/approve/{{ $req->id }}" method="POST" style="display:inline;">
                    @csrf
                    <input type="text" name="manager_note" placeholder="Reason..." required
                           style="padding:5px;border:1px solid #ccc;border-radius:5px;width:110px;">
                    <button style="background:#22c55e;color:white;padding:5px 10px;border:none;border-radius:5px;">
                        ✔
                    </button>
                </form>

                <!-- REJECT -->
                <form action="/admin/manager/reject/{{ $req->id }}" method="POST" style="display:inline;margin-left:5px;">
                    @csrf
                    <input type="text" name="manager_note" placeholder="Reason..." required
                           style="padding:5px;border:1px solid #ccc;border-radius:5px;width:110px;">
                    <button style="background:#ef4444;color:white;padding:5px 10px;border:none;border-radius:5px;">
                        ✖
                    </button>
                </form>
            </td>

        </tr>
        @empty
        <tr>
            <td colspan="6" style="text-align:center; padding:15px;">
                No pending requests
            </td>
        </tr>
        @endforelse

    </table>

</div>

@endsection