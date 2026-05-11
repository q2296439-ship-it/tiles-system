@extends('layouts.admin')

@section('content')

<div style="
    background:#fff;
    padding:25px;
    border-radius:16px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
">

    <div style="
        display:flex;
        justify-content:space-between;
        align-items:center;
        flex-wrap:wrap;
        gap:15px;
        margin-bottom:20px;
    ">

        <div>
            <h2 style="
                font-size:24px;
                font-weight:800;
                color:#dc2626;
                margin-bottom:5px;
            ">
                ⚠ Delete Transactions
            </h2>

            <p style="
                color:#6b7280;
                font-size:14px;
            ">
                Permanently delete whole OR transactions safely.
            </p>
        </div>

    </div>

    <div style="
        background:#fef2f2;
        border:1px solid #fecaca;
        color:#991b1b;
        padding:15px;
        border-radius:12px;
        font-size:14px;
    ">
        ⚠ WARNING: This module permanently deletes transaction records,
        related items, returns, and restores inventory stocks.
    </div>

</div>

@endsection