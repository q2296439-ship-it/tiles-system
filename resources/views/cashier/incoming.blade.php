@extends('layouts.cashier')

@section('content')

<style>
    .table-wrapper{
        background:white;
        padding:20px;
        border-radius:12px;
        box-shadow:0 10px 25px rgba(0,0,0,.05);
    }

    table{
        width:100%;
        border-collapse:collapse;
    }

    th{
        background:#f1f5f9;
        font-size:13px;
        color:#64748b;
        text-align:left;
    }

    th,td{
        padding:12px 10px;
        border-bottom:1px solid #e5e7eb;
        font-size:14px;
        vertical-align:middle;
    }

    th:nth-child(3),
    td:nth-child(3),
    th:nth-child(4),
    td:nth-child(4){
        text-align:center;
    }

    .status-ready{
        color:#16a34a;
        font-weight:700;
    }

    .input-box{
        width:100%;
        padding:7px 10px;
        border:1px solid #cbd5e1;
        border-radius:8px;
        font-size:13px;
        outline:none;
    }

    .btn-receive{
        background:#16a34a;
        color:white;
        padding:8px 14px;
        border-radius:8px;
        border:none;
        cursor:pointer;
        font-size:13px;
        font-weight:700;
        width:100%;
    }

    .btn-receive:hover{
        background:#15803d;
    }

    h2{
        font-size:22px;
        margin-bottom:15px;
        font-weight:900;
        color:#0f172a;
    }

    .small-note{
        font-size:12px;
        color:#64748b;
        margin-top:-8px;
        margin-bottom:15px;
    }
</style>

<h2>📦 Incoming Transfer Stock</h2>
<div class="small-note">Receive stock transfer with manual transfer number and audit remarks</div>

<div class="table-wrapper">

    <table>
        <tr>
            <th>Product</th>
            <th>From Branch</th>
            <th style="text-align:center;">Qty</th>
            <th style="text-align:center;">Status</th>
            <th>Transfer No.</th>
            <th>Remarks</th>
            <th>Action</th>
        </tr>

        @forelse($requests as $req)
        <tr>
            <td>{{ $req->product->name ?? '-' }}</td>
            <td>{{ $req->from_branch->name ?? '-' }}</td>
            <td style="text-align:center;">{{ $req->quantity }}</td>

            <td class="status-ready" style="text-align:center;">
                Ready to Receive
            </td>

            <td style="min-width:170px;">
                <form action="/cashier/receive/{{ $req->id }}" method="POST">
                    @csrf
                    <input 
                        type="text"
                        name="transfer_number"
                        class="input-box"
                        placeholder="Enter number"
                        required
                    >
            </td>

            <td style="min-width:220px;">
                    <input 
                        type="text"
                        name="receive_remarks"
                        class="input-box"
                        placeholder="Remarks / Notes"
                    >
            </td>

            <td style="min-width:140px;">
                    <button class="btn-receive">
                        ✔ Receive
                    </button>
                </form>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="7" style="text-align:center; padding:15px;">
                No incoming transfers
            </td>
        </tr>
        @endforelse

    </table>

</div>

@endsection