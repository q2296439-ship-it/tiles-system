<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            padding: 25px;
            color: #111;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .company {
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 1px;
        }

        .report {
            font-size: 14px;
            margin-top: 5px;
        }

        .range {
            font-size: 11px;
            color: #555;
            margin-top: 5px;
        }

        .divider {
            border-top: 2px solid #000;
            margin: 20px 0 25px;
        }

        .summary {
            width: 100%;
            margin-bottom: 25px;
        }

        .summary td {
            padding: 10px;
            border: 1px solid #000;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #111;
            color: #fff;
            text-align: left;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
        }

        td.right {
            text-align: right;
        }

        .footer {
            margin-top: 25px;
            text-align: right;
            font-weight: bold;
            font-size: 13px;
        }

        .signature {
            margin-top: 60px;
        }

        .line {
            border-top: 1px solid #000;
            width: 220px;
            margin-top: 40px;
        }
    </style>
</head>

<body>

<!-- HEADER -->
<div class="header">
    <div class="company">NICOLE TILES CENTER</div>
    <div class="report">Sales Report ({{ strtoupper($range) }})</div>
    <div class="range">
        Generated: {{ now()->format('F d, Y h:i A') }}
    </div>
</div>

<div class="divider"></div>

<!-- SUMMARY -->
<table class="summary">
    <tr>
        <td><strong>Total Sales:</strong> PHP {{ number_format($total, 2) }}</td>
        <td><strong>Transactions:</strong> {{ $transactionCount }}</td>
        <td><strong>Average:</strong> PHP {{ number_format($average, 2) }}</td>
    </tr>
</table>

<!-- TABLE -->
<table>
    <thead>
        <tr>
            <th>Date & Time</th>
            <th>Branch</th>
            <th>Cashier</th>
            <th>Product</th>
            <th style="text-align:center;">Qty</th>
            <th style="text-align:right;">Amount</th>
        </tr>
    </thead>

    <tbody>
        @foreach($sales as $sale)

            @foreach($sale->items as $item)
            <tr>
                <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y h:i A') }}</td>
                <td>{{ $sale->branch->name ?? 'N/A' }}</td>
                <td>{{ $sale->user->name ?? 'N/A' }}</td>
                <td>{{ $item->product->name ?? 'N/A' }}</td>
                <td style="text-align:center;">{{ $item->quantity }}</td>
                <td class="right">
                    PHP {{ number_format($item->quantity * $item->price, 2) }}
                </td>
            </tr>
            @endforeach

        @endforeach
    </tbody>
</table>

<!-- FOOTER -->
<div class="footer">
    GRAND TOTAL: PHP {{ number_format($total, 2) }}
</div>

<!-- SIGNATURE -->
<div class="signature">
    <div class="line"></div>
    <small>Authorized Signature</small>
</div>

</body>
</html>