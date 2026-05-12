<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cash Flow Report</title>

    <style>

        body{
            font-family: DejaVu Sans, sans-serif;
            padding:30px;
            color:#111827;
        }

        .title{
            text-align:center;
            font-size:24px;
            font-weight:bold;
            margin-bottom:5px;
        }

        .subtitle{
            text-align:center;
            font-size:12px;
            color:#6b7280;
            margin-bottom:30px;
        }

        .card{
            border:1px solid #d1d5db;
            border-radius:10px;
            padding:20px;
            margin-bottom:20px;
        }

        .cash{
            text-align:center;
        }

        .cash h2{
            margin:0;
            font-size:14px;
            color:#6b7280;
        }

        .cash h1{
            margin-top:10px;
            font-size:38px;
            color:#16a34a;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
        }

        table th{
            background:#2563eb;
            color:white;
            padding:12px;
            text-align:left;
            font-size:13px;
        }

        table td{
            border:1px solid #d1d5db;
            padding:12px;
            font-size:13px;
        }

        .right{
            text-align:right;
        }

        .green{
            color:#16a34a;
            font-weight:bold;
        }

        .red{
            color:#dc2626;
            font-weight:bold;
        }

        .blue{
            color:#2563eb;
            font-weight:bold;
        }

    </style>
</head>
<body>

    <div class="title">
        BRANCH CASH FLOW REPORT
    </div>

    <div class="subtitle">
        Daily Financial Position Report
    </div>

    <div class="card cash">

        <h2>TOTAL CASH</h2>

        <h1>
            ₱{{ number_format($totalCash,2) }}
        </h1>

        <p>
            Date: {{ $date }}
        </p>

    </div>

    <table>

        <thead>
            <tr>
                <th>Description</th>
                <th class="right">Amount</th>
            </tr>
        </thead>

        <tbody>

            <tr>
                <td>Actual Deposit</td>
                <td class="right green">
                    ₱{{ number_format($actualDeposit,2) }}
                </td>
            </tr>

            <tr>
                <td>Expenses</td>
                <td class="right red">
                    ₱{{ number_format($expenses,2) }}
                </td>
            </tr>

            <tr>
                <td>Total Cash In</td>
                <td class="right green">
                    ₱{{ number_format($cashIn,2) }}
                </td>
            </tr>

            <tr>
                <td>Total Cash Out</td>
                <td class="right red">
                    ₱{{ number_format($cashOut,2) }}
                </td>
            </tr>

            <tr>
                <td class="blue">
                    NET CASH
                </td>

                <td class="right blue">
                    ₱{{ number_format($totalCash,2) }}
                </td>
            </tr>

        </tbody>

    </table>

</body>
</html>