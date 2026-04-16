<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Request Access Report</title>

    <style>
        body{
            font-family: DejaVu Sans, sans-serif;
            font-size:12px;
        }

        h2{
            margin:0 0 10px 0;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:15px;
        }

        th, td{
            border:1px solid #000;
            padding:6px;
            font-size:11px;
            text-align:left;
        }

        th{
            background:#f2f2f2;
        }

        .center{
            text-align:center;
        }
    </style>
</head>
<body>

    <h2>Request Access Report</h2>
    <p>Generated: {{ now()->format('M d, Y h:i A') }}</p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Branch</th>
                <th>Date Closed</th>
                <th>Net</th>
                <th>Actual</th>
                <th>Variance</th>
                <th>Closed By</th>
                <th>Closed At</th>
                <th>Status</th>
            </tr>
        </thead>

        <tbody>
        @forelse($rows as $row)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $row->branch_name }}</td>
                <td>{{ $row->deposit_date }}</td>
                <td>{{ number_format($row->net_amount,2) }}</td>
                <td>{{ number_format($row->actual_amount,2) }}</td>
                <td>{{ number_format($row->variance,2) }}</td>
                <td>{{ $row->user->name ?? 'N/A' }}</td>
                <td>{{ $row->created_at }}</td>
                <td>{{ ucfirst($row->status) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="9" class="center">No records found.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

</body>
</html>