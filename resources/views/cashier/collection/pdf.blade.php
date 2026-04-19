<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Collection Report</title>

    <style>
        body{
            font-family: DejaVu Sans, sans-serif;
            font-size:11px;
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

        .summary{
            margin-top:12px;
            font-size:11px;
            line-height:1.8;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:18px;
        }

        table th,
        table td{
            border:1px solid #ccc;
            padding:5px;
            vertical-align:top;
        }

        table th{
            background:#f3f4f6;
            font-size:10px;
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
            font-size:10px;
            color:#444;
        }

        .status{
            font-weight:bold;
            text-transform:uppercase;
            font-size:10px;
        }
    </style>
</head>

<body>

@php
    $totalSales = $collections->sum('total_amount');
    $actualCollection = $collections->sum(function($row){
        return $row->paid_amount ?? 0;
    });
@endphp

<div class="top-line"></div>

<div class="header">
    <h2>NICOLE TILES CENTER</h2>
    <div class="sub-title">Collection Receipt Report</div>

    <div class="info">
        Generated: {{ now()->format('F d, Y h:i A') }} <br>
        Branch: {{ $branchName ?? 'Current Branch' }} <br>
        Date: {{ $selectedDate ?? date('Y-m-d') }}
    </div>

    <div class="summary">
        Total Receipts: <strong>{{ count($collections) }}</strong> &nbsp;&nbsp;&nbsp;
        Total Sales: <strong>₱{{ number_format($totalSales, 2) }}</strong> &nbsp;&nbsp;&nbsp;
        Actual Collection: <strong>₱{{ number_format($actualCollection, 2) }}</strong>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th width="4%">#</th>
            <th width="8%">Receipt</th>
            <th width="11%">Customer</th>
            <th width="20%">Products</th>
            <th width="6%">Qty</th>
            <th width="10%">Sales</th>
            <th width="10%">Paid</th>
            <th width="10%">Balance</th>
            <th width="9%">Status</th>
            <th width="12%">Cashier</th>
        </tr>
    </thead>

    <tbody>

        @forelse($collections as $index => $row)

        @php
            $status = strtolower($row->record_type ?? $row->status ?? 'saved');
        @endphp

        <tr>
            <td class="text-center">{{ $index + 1 }}</td>

            <td>{{ $row->display_receipt_no ?? $row->receipt_no }}</td>

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
                ₱{{ number_format($row->total_amount, 2) }}
            </td>

            <td class="text-right">
                ₱{{ number_format($row->paid_amount ?? 0, 2) }}
            </td>

            <td class="text-right">
                ₱{{ number_format($row->balance ?? 0, 2) }}
            </td>

            <td class="text-center status">
                {{ ucfirst($status) }}
            </td>

            <td class="small">
                {{ $row->user->name ?? 'Cashier' }}<br>
                {{ $row->created_at->format('h:i A') }}
            </td>
        </tr>

        @empty
        <tr>
            <td colspan="10" class="text-center">
                No collection records found.
            </td>
        </tr>
        @endforelse

        <tr class="total-row">
            <td colspan="5">TOTAL</td>
            <td class="text-right">₱{{ number_format($totalSales, 2) }}</td>
            <td class="text-right">₱{{ number_format($actualCollection, 2) }}</td>
            <td class="text-right">-</td>
            <td colspan="2"></td>
        </tr>

    </tbody>
</table>

</body>
</html>