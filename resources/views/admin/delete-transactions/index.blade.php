@extends('layouts.admin')

@section('content')

<div style="
    background:#fff;
    padding:25px;
    border-radius:18px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
">

    {{-- HEADER --}}
    <div style="
        display:flex;
        justify-content:space-between;
        align-items:center;
        flex-wrap:wrap;
        gap:15px;
        margin-bottom:25px;
    ">

        <div>
            <h2 style="
                font-size:28px;
                font-weight:800;
                color:#dc2626;
                margin-bottom:6px;
            ">
                ⚠ Delete Transactions
            </h2>

            <p style="
                color:#6b7280;
                font-size:14px;
            ">
                Search and permanently delete whole OR transactions safely.
            </p>
        </div>

    </div>

    {{-- WARNING --}}
    <div style="
        background:#fef2f2;
        border:1px solid #fecaca;
        color:#991b1b;
        padding:16px;
        border-radius:12px;
        margin-bottom:25px;
        font-size:14px;
        line-height:1.6;
    ">
        ⚠ WARNING: This module permanently deletes transaction records,
        related items, returns, and restores inventory stocks.
    </div>

    {{-- SEARCH FILTER --}}
    <form method="GET" action="" style="margin-bottom:30px;">

        <div style="
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
            gap:15px;
        ">

            {{-- BRANCH --}}
            <div>
                <label style="
                    display:block;
                    margin-bottom:6px;
                    font-size:13px;
                    font-weight:700;
                    color:#374151;
                ">
                    Select Branch
                </label>

                <select name="branch_id" style="
                    width:100%;
                    padding:12px;
                    border:1px solid #d1d5db;
                    border-radius:12px;
                    font-size:14px;
                    outline:none;
                ">

                    <option value="">-- Select Branch --</option>

                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ $selectedBranch == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach

                </select>
            </div>

            {{-- DATE --}}
            <div>
                <label style="
                    display:block;
                    margin-bottom:6px;
                    font-size:13px;
                    font-weight:700;
                    color:#374151;
                ">
                    Transaction Date
                </label>

                <input
                    type="date"
                    name="date"
                    value="{{ $selectedDate }}"
                    style="
                        width:100%;
                        padding:12px;
                        border:1px solid #d1d5db;
                        border-radius:12px;
                        font-size:14px;
                        outline:none;
                    "
                >
            </div>

            {{-- OR NUMBER --}}
            <div>
                <label style="
                    display:block;
                    margin-bottom:6px;
                    font-size:13px;
                    font-weight:700;
                    color:#374151;
                ">
                    OR Number
                </label>

                <input
                    type="text"
                    name="receipt_no"
                    value="{{ $receiptNo }}"
                    placeholder="Enter OR Number"
                    style="
                        width:100%;
                        padding:12px;
                        border:1px solid #d1d5db;
                        border-radius:12px;
                        font-size:14px;
                        outline:none;
                    "
                >
            </div>

        </div>

        {{-- BUTTON --}}
        <div style="
            margin-top:20px;
            display:flex;
            justify-content:flex-end;
        ">

            <button type="submit" style="
                background:#2563eb;
                color:#fff;
                border:none;
                padding:12px 24px;
                border-radius:12px;
                font-size:14px;
                font-weight:700;
                cursor:pointer;
            ">
                🔍 Search Transaction
            </button>

        </div>

    </form>

    {{-- RESULT SECTION --}}
    <div style="
        border:1px solid #e5e7eb;
        border-radius:16px;
        overflow:hidden;
    ">

        {{-- RESULT HEADER --}}
        <div style="
            background:#f8fafc;
            padding:18px;
            border-bottom:1px solid #e5e7eb;
        ">

            <h3 style="
                font-size:18px;
                font-weight:800;
                color:#111827;
                margin-bottom:5px;
            ">
                Transaction Preview
            </h3>

            <p style="
                font-size:13px;
                color:#6b7280;
            ">
                Transaction details will appear here after searching.
            </p>

        </div>

        {{-- PREVIEW CONTENT --}}
        <div style="padding:20px;">

            @if($collection)

                {{-- INFO GRID --}}
                <div style="
                    display:grid;
                    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
                    gap:15px;
                    margin-bottom:25px;
                ">

                    <div style="background:#f9fafb;padding:15px;border-radius:12px;">
                        <small style="color:#6b7280;">OR Number</small>
                        <div style="font-weight:700;margin-top:4px;">
                            {{ $collection->receipt_no }}
                        </div>
                    </div>

                    <div style="background:#f9fafb;padding:15px;border-radius:12px;">
                        <small style="color:#6b7280;">Branch</small>
                        <div style="font-weight:700;margin-top:4px;">
                            {{ $collection->branch->name ?? 'N/A' }}
                        </div>
                    </div>

                    <div style="background:#f9fafb;padding:15px;border-radius:12px;">
                        <small style="color:#6b7280;">Cashier</small>
                        <div style="font-weight:700;margin-top:4px;">
                            {{ $collection->user->name ?? 'N/A' }}
                        </div>
                    </div>

                    <div style="background:#f9fafb;padding:15px;border-radius:12px;">
                        <small style="color:#6b7280;">Gross Sales</small>
                        <div style="
                            font-weight:800;
                            margin-top:4px;
                            color:#16a34a;
                        ">
                            ₱{{ number_format($collection->total_amount, 2) }}
                        </div>
                    </div>

                </div>

                {{-- COLLECTION ITEMS --}}
                <h4 style="
                    font-size:16px;
                    font-weight:800;
                    margin-bottom:12px;
                    color:#111827;
                ">
                    Collection Items
                </h4>

                <div style="overflow-x:auto;margin-bottom:30px;">

                    <table style="
                        width:100%;
                        border-collapse:collapse;
                        min-width:700px;
                    ">

                        <thead>
                            <tr style="background:#f3f4f6;text-align:left;">
                                <th style="padding:12px;">Product</th>
                                <th style="padding:12px;">Qty</th>
                                <th style="padding:12px;">Price</th>
                                <th style="padding:12px;">Subtotal</th>
                            </tr>
                        </thead>

                        <tbody>

                            @foreach($collection->items as $item)

                                <tr style="border-bottom:1px solid #e5e7eb;">

                                    <td style="padding:12px;">
                                        {{ $item->description }}
                                    </td>

                                    <td style="padding:12px;">
                                        {{ $item->qty }}
                                    </td>

                                    <td style="padding:12px;">
                                        ₱{{ number_format($item->unit_price, 2) }}
                                    </td>

                                    <td style="padding:12px;">
                                        ₱{{ number_format($item->amount, 2) }}
                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

                {{-- SALES --}}
                @if(isset($sales) && $sales->count())

                    <h4 style="
                        font-size:16px;
                        font-weight:800;
                        margin-bottom:12px;
                        color:#111827;
                    ">
                        Sales Records
                    </h4>

                    <div style="margin-bottom:30px;">

                        @foreach($sales as $sale)

                            <div style="
                                background:#f9fafb;
                                padding:15px;
                                border-radius:12px;
                                margin-bottom:15px;
                            ">

                                Sale ID: {{ $sale->id }}

                            </div>

                        @endforeach

                    </div>

                @endif

                {{-- RETURNS --}}
@if(isset($returns) && $returns->count())

    <h4 style="
        font-size:16px;
        font-weight:800;
        margin-bottom:12px;
        color:#111827;
    ">
        Return Records
    </h4>

    @foreach($returns as $return)

        {{-- RETURN INFO --}}
        <div style="
            background:#fff7ed;
            padding:15px;
            border-radius:12px;
            margin-bottom:15px;
            border:1px solid #fed7aa;
        ">

            <div style="
                display:flex;
                justify-content:space-between;
                flex-wrap:wrap;
                gap:10px;
                margin-bottom:10px;
            ">

                <div>
                    <strong>Return No:</strong>
                    {{ $return->return_no }}
                </div>

                <div>
                    <strong>Receipt No:</strong>
                    {{ $return->receipt_no }}
                </div>

                <div>
                    <strong>Total:</strong>
                    ₱{{ number_format($return->total_amount, 2) }}
                </div>

            </div>

            <div style="
                margin-bottom:15px;
                color:#92400e;
                font-size:14px;
            ">
                <strong>Reason:</strong>
                {{ $return->reason }}
            </div>

            {{-- RETURN ITEMS --}}
            <div style="overflow-x:auto;">

                <table style="
                    width:100%;
                    border-collapse:collapse;
                    min-width:700px;
                    background:#fff;
                ">

                    <thead>

                        <tr style="
                            background:#fef3c7;
                            text-align:left;
                        ">

                            <th style="padding:12px;">Product</th>
                            <th style="padding:12px;">Qty</th>
                            <th style="padding:12px;">Price</th>
                            <th style="padding:12px;">Subtotal</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach($return->items as $item)

                            <tr style="
                                border-bottom:1px solid #e5e7eb;
                            ">

                                <td style="padding:12px;">
                                    {{ $item->description }}
                                </td>

                                <td style="padding:12px;">
                                    {{ $item->qty }}
                                </td>

                                <td style="padding:12px;">
                                    ₱{{ number_format($item->unit_price, 2) }}
                                </td>

                                <td style="padding:12px;">
                                    ₱{{ number_format($item->amount, 2) }}
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        </div>

    @endforeach

@endif
        </div>

    </div>

</div>

@endsection