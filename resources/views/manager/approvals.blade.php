@extends('layouts.manager')

@section('content')

<h2 style="margin-bottom:20px;">🧾 Approval Center</h2>

{{-- ===================== --}}
{{-- 🔥 TRANSFER IN --}}
{{-- ===================== --}}
<div style="background:white; padding:20px; border-radius:10px; margin-bottom:20px;">
    <h3 style="margin-bottom:15px;">⬅️ Transfer In Requests</h3>

    <table style="width:100%; border-collapse:collapse;">
        <tr style="background:#f1f5f9;">
            <th style="padding:10px;">Product</th>
            <th>From → To</th>
            <th>Qty</th>
            <th>Status</th>
            <th style="text-align:center;">Action</th>
        </tr>

        @forelse($requests->where('type','IN_REQUEST') as $req)
        <tr>
            <td style="padding:10px;">{{ $req->product->name ?? '-' }}</td>
            <td>{{ $req->from_branch->name ?? '-' }} → {{ $req->branch->name ?? '-' }}</td>
            <td>{{ $req->quantity }}</td>
            <td style="color:orange;">{{ ucfirst($req->status) }}</td>

            <td style="text-align:center;">
                <div style="display:flex; justify-content:center; gap:5px;">

                    <!-- APPROVE -->
                    <form action="/admin/manager/approve/{{ $req->id }}" method="POST">
                        @csrf
                        <input type="text" name="manager_note" placeholder="Reason..." required
                               style="padding:5px;border:1px solid #ccc;border-radius:5px;width:100px;">
                        <button style="background:#22c55e;color:white;padding:5px 8px;border:none;border-radius:5px;">
                            ✔
                        </button>
                    </form>

                    <!-- REJECT -->
                    <form action="/admin/manager/reject/{{ $req->id }}" method="POST">
                        @csrf
                        <input type="text" name="manager_note" placeholder="Reason..." required
                               style="padding:5px;border:1px solid #ccc;border-radius:5px;width:100px;">
                        <button style="background:#ef4444;color:white;padding:5px 8px;border:none;border-radius:5px;">
                            ✖
                        </button>
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
        <tr style="background:#f1f5f9;">
            <th style="padding:10px;">Product</th>
            <th>From → To</th>
            <th>Qty</th>
            <th>Status</th>
            <th style="text-align:center;">Action</th>
        </tr>

        @forelse($requests->where('type','OUT') as $req)
        <tr>
            <td style="padding:10px;">{{ $req->product->name ?? '-' }}</td>
            <td>{{ $req->from_branch->name ?? '-' }} → {{ $req->branch->name ?? '-' }}</td>
            <td>{{ $req->quantity }}</td>
            <td style="color:orange;">{{ ucfirst($req->status) }}</td>

            <td style="text-align:center;">
                <div style="display:flex; justify-content:center; gap:5px;">

                    <form action="/admin/manager/approve/{{ $req->id }}" method="POST">
                        @csrf
                        <input type="text" name="manager_note" placeholder="Reason..." required
                               style="padding:5px;border:1px solid #ccc;border-radius:5px;width:100px;">
                        <button style="background:#22c55e;color:white;padding:5px 8px;border:none;border-radius:5px;">
                            ✔
                        </button>
                    </form>

                    <form action="/admin/manager/reject/{{ $req->id }}" method="POST">
                        @csrf
                        <input type="text" name="manager_note" placeholder="Reason..." required
                               style="padding:5px;border:1px solid #ccc;border-radius:5px;width:100px;">
                        <button style="background:#ef4444;color:white;padding:5px 8px;border:none;border-radius:5px;">
                            ✖
                        </button>
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