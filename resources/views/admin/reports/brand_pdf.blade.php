<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales per Brand Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        .header {
            margin-bottom: 20px;
        }

        h2 {
            margin: 0;
        }

        .info {
            font-size: 11px;
            color: #555;
            margin-top: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th, table td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        table th {
            background: #f3f4f6;
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .top-row {
            background: #d1fae5;
            font-weight: bold;
        }

        .total-row {
            font-weight: bold;
            background: #f9fafb;
        }
    </style>
</head>

<body>

<div class="header">
    <h2>📊 SALES PER BRAND REPORT</h2>

    <div class="info">
        Date:
        {{ request('start_date') ?? 'All' }}
        -
        {{ request('end_date') ?? 'All' }}
        <br>

        @if(request('branch_id'))
            Branch: {{ $data->first()->branch ?? 'Selected Branch' }} <br>
        @else
            Branch: All Branches <br>
        @endif

        Generated: {{ now()->format('Y-m-d h:i A') }}
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Brand</th>
            <th class="text-right">Total Sales (₱)</th>
            <th class="text-right">% Share</th>
        </tr>
    </thead>

    <tbody>
        @php $total = $totals->sum(); @endphp

        @foreach($data as $index => $row)
        <tr class="{{ $index == 0 ? 'top-row' : '' }}">
            <td>{{ $index + 1 }}</td>
            <td>{{ $row->brand }}</td>
            <td class="text-right">₱{{ number_format($row->total, 2) }}</td>
            <td class="text-right">
                {{ $total > 0 ? number_format(($row->total / $total) * 100, 2) : 0 }}%
            </td>
        </tr>
        @endforeach

        <!-- 🔥 TOTAL ROW -->
        <tr class="total-row">
            <td colspan="2">TOTAL</td>
            <td class="text-right">₱{{ number_format($total, 2) }}</td>
            <td class="text-right">100%</td>
        </tr>
    </tbody>
</table>

</body>
</html>