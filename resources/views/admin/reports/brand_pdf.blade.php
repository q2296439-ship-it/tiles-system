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

        h2 {
            margin-bottom: 5px;
        }

        .header {
            margin-bottom: 20px;
        }

        .info {
            font-size: 11px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table th, table td {
            border: 1px solid #ccc;
            padding: 6px;
            text-align: left;
        }

        table th {
            background: #f3f4f6;
        }

        .text-right {
            text-align: right;
        }
    </style>
</head>

<body>

<div class="header">
    <h2>📊 Sales per Brand Report</h2>

    <div class="info">
        Date: {{ request('start_date') }} - {{ request('end_date') }} <br>
        Generated: {{ now()->format('Y-m-d h:i A') }}
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Brand</th>
            <th>Total Sales</th>
            <th>% Share</th>
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
    </tbody>
</table>

</body>
</html>