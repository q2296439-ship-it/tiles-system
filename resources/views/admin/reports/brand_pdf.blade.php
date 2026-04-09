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

        .top-line {
            width: 100%;
            height: 5px;
            background: #111;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            font-size: 16px;
            letter-spacing: 1px;
        }

        .sub-title {
            font-size: 12px;
            margin-top: 2px;
            color: #333;
        }

        .info {
            margin-top: 10px;
            font-size: 11px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        table th, table td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        table th {
            background: #f3f4f6;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            background: #f1f5f9;
        }
    </style>
</head>

<body>

<div class="top-line"></div>

<div class="header">
    <h2>NICOLE TILES CENTER</h2>
    <div class="sub-title">Brand Sales Performance Report</div>

    <div class="info">
        Generated: {{ now()->format('F d, Y h:i A') }} <br>

        Branch:
        @if(request('branch_id'))
            {{ $data->first()->branch ?? 'Selected Branch' }}
        @else
            All Branches
        @endif
        <br>

        Date:
        {{ request('start_date') ?? 'All' }}
        -
        {{ request('end_date') ?? 'All' }}
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
        <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row->brand }}</td>
            <td class="text-right">₱{{ number_format($row->total, 2) }}</td>
            <td class="text-right">
                {{ $total > 0 ? number_format(($row->total / $total) * 100, 2) : 0 }}%
            </td>
        </tr>
        @endforeach

        <!-- TOTAL -->
        <tr class="total-row">
            <td colspan="2">TOTAL</td>
            <td class="text-right">₱{{ number_format($total, 2) }}</td>
            <td class="text-right">100%</td>
        </tr>
    </tbody>
</table>

</body>
</html>