<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nicole Tiles Center - Branch Report</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #111;
            padding: 30px;
        }

        .topbar {
            height: 6px;
            background: #111;
            margin-bottom: 15px;
        }

        .header {
            margin-bottom: 20px;
        }

        .company {
            font-size: 18px;
            font-weight: bold;
        }

        .report-title {
            font-size: 13px;
            color: #444;
        }

        .date {
            font-size: 11px;
            color: #777;
            margin-top: 5px;
        }

        .summary {
            margin-top: 15px;
            margin-bottom: 25px;
        }

        .summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary td {
            width: 50%;
            padding: 10px;
        }

        .summary-box {
            border: 1px solid #000;
            padding: 10px;
        }

        .label {
            font-size: 11px;
            color: #555;
        }

        .value {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }

        table.main {
            width: 100%;
            border-collapse: collapse;
        }

        table.main th {
            background: #111;
            color: #fff;
            font-size: 12px;
        }

        table.main th, 
        table.main td {
            padding: 8px;
            border: 1px solid #000;
        }

        td.right {
            text-align: right;
        }

        .top {
            background: #e7fbe9;
            font-weight: bold;
        }

        .footer {
            margin-top: 25px;
        }

        .grand {
            text-align: right;
            font-size: 14px;
            font-weight: bold;
        }

        .signature {
            margin-top: 50px;
        }

        .line {
            width: 200px;
            border-top: 1px solid #000;
            margin-top: 40px;
        }

    </style>
</head>

<body>

    <div class="topbar"></div>

    <!-- HEADER -->
    <div class="header">
        <div class="company">NICOLE TILES CENTER</div>
        <div class="report-title">Branch Sales Performance Report</div>

        <!-- 🔥 NEW: FILTER INFO -->
        <div class="date">
            Generated: {{ now()->format('F d, Y h:i A') }} <br>

            @if(request('branch_id'))
                Branch: {{ $branches->first()->branch_name ?? 'N/A' }} <br>
            @else
                Branch: All Branches <br>
            @endif

            @if(request('start_date') && request('end_date'))
                Date: {{ request('start_date') }} to {{ request('end_date') }}
            @endif
        </div>
    </div>

    <!-- SUMMARY -->
    <div class="summary">
        <table>
            <tr>
                <td>
                    <div class="summary-box">
                        <div class="label">TOTAL BRANCHES</div>
                        <div class="value">{{ $branches->count() }}</div>
                    </div>
                </td>

                <td>
                    <div class="summary-box">
                        <div class="label">TOTAL SALES</div>
                        <div class="value">PHP {{ number_format($grandTotal, 2) }}</div>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- TABLE -->
    <table class="main">
        <thead>
            <tr>
                <th width="5%">#</th>
                <th>Branch</th>
                <th width="25%">Total Sales</th>
                <th width="20%">Transactions</th>
            </tr>
        </thead>

        <tbody>
            @foreach($branches as $index => $branch)
            <tr class="{{ $index == 0 ? 'top' : '' }}">
                <td>{{ $index + 1 }}</td>

                <td>{{ $branch->branch_name }}</td>

                <td class="right">
                    PHP {{ number_format($branch->total_sales, 2) }}
                </td>

                <td class="right">
                    {{ $branch->transactions }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- FOOTER -->
    <div class="footer">
        <div class="grand">
            GRAND TOTAL: PHP {{ number_format($grandTotal, 2) }}
        </div>

        <div class="signature">
            <div class="line"></div>
            <small>Authorized Signature</small>
        </div>
    </div>

</body>
</html>