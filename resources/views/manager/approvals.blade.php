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
        </tr>
        @empty
        <tr>
            <td colspan="5" style="text-align:center; padding:15px;">
                No pending requests
            </td>
        </tr>
        @endforelse

    </table>

</div>

@endsection