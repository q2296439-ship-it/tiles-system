@extends('layouts.cashier')

@section('content')

<h2 style="margin-bottom:20px;">📦 Incoming Transfer Stock</h2>

<div style="background:white; padding:20px; border-radius:10px;">

    <table style="width:100%; border-collapse:collapse;">
        <tr style="background:#f9fafb;">
            <th style="padding:10px;">Product</th>
            <th>From Branch</th>
            <th>Qty</th>
            <th>Status</th>
            <th style="text-align:center;">Action</th>
        </tr>

        @forelse($requests as $req)
        <tr>
            <td>{{ $req->product->name ?? '-' }}</td>
            <td>{{ $req->from_branch->name ?? '-' }}</td>
            <td style="text-align:center;">{{ $req->quantity }}</td>

            <td style="text-align:center; color:green;">
                Ready to Receive
            </td>

            <td style="text-align:center;">
                <form action="/cashier/receive/{{ $req->id }}" method="POST">
                    @csrf
                    <button style="
                        background:#16a34a;
                        color:white;
                        padding:6px 12px;
                        border:none;
                        border-radius:6px;
                        cursor:pointer;
                    ">
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