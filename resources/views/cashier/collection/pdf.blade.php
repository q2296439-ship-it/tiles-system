<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collection Report</title>

    <style>
        body{
            font-family: DejaVu Sans, sans-serif;
            font-size:12px;
        }

        .top-line{
            width:100%;
            height:5px;
            background:#111;
            margin-bottom:20px;
        }

        .header h2{
            margin:0;
            font-size:16px;
            letter-spacing:1px;
        }

        .sub-title{
            font-size:12px;
            margin-top:2px;
            color:#333;
        }

        .info{
            margin-top:10px;
            font-size:11px;
            color:#555;
            line-height:1.6;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
        }

        table th,
        table td{
            border:1px solid #ccc;
            padding:6px;
            vertical-align:top;
        }

        table th{
            background:#f3f4f6;
        }

        .text-right{
            text-align:right;
        }

        .text-center{
            text-align:center;
        }

        .total-row{
            font-weight:bold;
            background:#f1f5f9;
        }

        .small{
            font-size:11px;
            color:#444;
        }
    </style>
</head>

<body>

<div class="top-line"></div>

<div class="header">
    <h2>NICOLE TILES CENTER</h2>
    <div class="sub-title">Collection Receipt Report</div>

    <div class="info">
        Generated: {{ now()->format('F d, Y h:i A') }} <br>
        Branch: {{ auth()->user()->branch->name ?? 'Current Branch' }} <br>
        Date: {{ $selectedDate ?? date('Y-m-d') }}
    </div>
</div>

<table>
    <thead>
        <tr>
            <th width="4%">#</th>
            <th width="10%">Receipt No</th>
            <th width="12%">Customer</th>
            <th width="28%">Products</th>
            <th width="8%">Qty</th>
            <th width="12%">Total</th>
            <th width="14%">Cashier</th>
            <th width="12%">Time</th>
        </tr>
    </thead>

    <tbody>

        @forelse($collections as $index => $row)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>

                <td>
                    {{ $row->display_receipt_no ?? $row->receipt_no }}
                </td>

                <td>{{ $row->customer_name }}</td>

                <td class="small">
                    @foreach($row->items as $item)
                        {{ $item->description }}<br>
                    @endforeach
                </td>

                <td class="text-center small">
                    @foreach($row->items as $item)
                        {{ $item->qty }}<br>
                    @endforeach
                </td>

                <td class="text-right">
                    @if(($row->record_type ?? '') == 'returned')
                        -₱{{ number_format($row->total_amount, 2) }}
                    @elseif(($row->record_type ?? '') == 'cancelled')
                        ₱0.00
                    @else
                        ₱{{ number_format($row->total_amount, 2) }}
                    @endif
                </td>

                <td>{{ $row->user->name ?? 'Cashier' }}</td>
                <td>{{ $row->created_at->format('h:i A') }}</td>
            </tr>

        @empty
            <tr>
                <td colspan="8" class="text-center">No collection records found.</td>
            </tr>
        @endforelse

        <tr class="total-row">
            <td colspan="5">TOTAL</td>
            <td class="text-right">₱{{ number_format($total, 2) }}</td>
            <td colspan="2"></td>
        </tr>

    </tbody>
</table>

</body>
</html>