<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Deposit Report</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            font-size:12px;
            color:#111;
            padding:30px;
        }

        .topbar{
            height:6px;
            background:#111;
            margin-bottom:15px;
        }

        .company{
            font-size:18px;
            font-weight:bold;
        }

        .title{
            font-size:13px;
            color:#444;
            margin-bottom:10px;
        }

        .date{
            font-size:11px;
            color:#666;
            margin-bottom:20px;
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th,td{
            border:1px solid #000;
            padding:7px;
            font-size:12px;
        }

        th{
            background:#111;
            color:#fff;
        }

        .right{
            text-align:right;
        }

        .center{
            text-align:center;
        }

        .footer{
            margin-top:20px;
            text-align:right;
            font-weight:bold;
        }
    </style>
</head>
<body>

<div class="topbar"></div>

<div class="company">NICOLE TILES CENTER</div>
<div class="title">Deposit Report</div>
<div class="date">
    Generated: {{ now()->format('F d, Y h:i A') }} <br>
    Date: {{ $date }}
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Branch</th>
            <th>Date</th>
            <th>Expected</th>
            <th>Actual</th>
            <th>Variance</th>
        </tr>
    </thead>

    <tbody>
        @foreach($rows as $row)
        <tr>
            <td class="center">{{ $loop->iteration }}</td>
            <td>{{ $row->branch_name ?? '-' }}</td>
            <td>{{ $row->deposit_date }}</td>
            <td class="right">{{ number_format($row->net_amount,2) }}</td>
            <td class="right">{{ number_format($row->actual_amount,2) }}</td>
            <td class="right">{{ number_format($row->variance,2) }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    Total Deposit:
    ₱{{ number_format($rows->sum('actual_amount'),2) }}
</div>

</body>
</html>