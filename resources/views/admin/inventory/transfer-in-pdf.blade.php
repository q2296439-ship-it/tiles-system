<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nicole Tiles Center - Transfer In Report</title>

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

        .header{
            margin-bottom:20px;
        }

        .company{
            font-size:18px;
            font-weight:bold;
        }

        .report-title{
            font-size:13px;
            color:#444;
        }

        .date{
            font-size:11px;
            color:#666;
            margin-top:5px;
            line-height:1.6;
        }

        .summary{
            margin-top:15px;
            margin-bottom:25px;
        }

        .summary table{
            width:100%;
            border-collapse:collapse;
        }

        .summary td{
            width:33.33%;
            padding:8px;
        }

        .summary-box{
            border:1px solid #000;
            padding:10px;
        }

        .label{
            font-size:11px;
            color:#555;
        }

        .value{
            font-size:14px;
            font-weight:bold;
            margin-top:5px;
        }

        table.main{
            width:100%;
            border-collapse:collapse;
        }

        table.main th{
            background:#111;
            color:#fff;
            font-size:12px;
        }

        table.main th,
        table.main td{
            border:1px solid #000;
            padding:7px;
        }

        td.center{
            text-align:center;
        }

        .footer{
            margin-top:25px;
        }

        .grand{
            text-align:right;
            font-size:14px;
            font-weight:bold;
        }

        .signature{
            margin-top:50px;
        }

        .line{
            width:220px;
            border-top:1px solid #000;
            margin-top:40px;
        }
    </style>
</head>

<body>

<div class="topbar"></div>

<div class="header">
    <div class="company">NICOLE TILES CENTER</div>
    <div class="report-title">Transfer In History Report</div>

    <div class="date">
        Generated: {{ now()->format('F d, Y h:i A') }} <br>

        @if(request('status'))
            Status Filter: {{ ucfirst(request('status')) }} <br>
        @else
            Status Filter: All Status <br>
        @endif

        @if(request('search'))
            Search: {{ request('search') }}
        @endif
    </div>
</div>

<div class="summary">
    <table>
        <tr>
            <td>
                <div class="summary-box">
                    <div class="label">TOTAL RECORDS</div>
                    <div class="value">{{ $transfers->count() }}</div>
                </div>
            </td>

            <td>
                <div class="summary-box">
                    <div class="label">COMPLETED</div>
                    <div class="value">
                        {{ $transfers->where('status','completed')->count() }}
                    </div>
                </div>
            </td>

            <td>
                <div class="summary-box">
                    <div class="label">TOTAL QUANTITY</div>
                    <div class="value">
                        {{ $transfers->sum('quantity') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

<table class="main">
    <thead>
        <tr>
            <th width="5%">#</th>
            <th>Product</th>
            <th>From Branch</th>
            <th>To Branch</th>
            <th width="10%">Qty</th>
            <th width="14%">Status</th>
            <th>Requested By</th>
            <th>Date</th>
        </tr>
    </thead>

    <tbody>
        @foreach($transfers as $index => $t)
        <tr>
            <td class="center">{{ $index + 1 }}</td>
            <td>{{ $t->product->name ?? '-' }}</td>

            <td>
                {{ $t->from_branch->name ?? $t->from_branch_name ?? '-' }}
            </td>

            <td>
                {{ $t->branch->name ?? $t->to_branch_name ?? '-' }}
            </td>

            <td class="center">{{ $t->quantity }}</td>
            <td class="center">{{ strtoupper($t->status) }}</td>
            <td>{{ $t->requester->name ?? '-' }}</td>
            <td>{{ $t->created_at->format('Y-m-d') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    <div class="grand">
        TOTAL QTY: {{ $transfers->sum('quantity') }}
    </div>

    <div class="signature">
        <div class="line"></div>
        <small>Authorized Signature</small>
    </div>
</div>

</body>
</html>