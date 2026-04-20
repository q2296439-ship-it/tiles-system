<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Deposit Slip</title>

<style>
body{
    font-family: Arial, sans-serif;
    font-size:12px;
    color:#111;
    padding:25px;
}
.box{
    border:2px solid #000;
    padding:18px;
}
.header{
    text-align:center;
    margin-bottom:15px;
}
.company{
    font-size:22px;
    font-weight:bold;
}
.sub{
    font-size:13px;
    margin-top:3px;
}
.info-table{
    width:100%;
    border:none;
    margin-bottom:12px;
}
.info-table td{
    border:none;
    padding:4px 0;
    text-align:left;
    vertical-align:middle;
}
.field-line{
    display:inline-block;
    border-bottom:1px solid #000;
    min-width:220px;
    padding:2px 6px;
}
.field-line.small{
    min-width:170px;
}

table.main{
    width:100%;
    border-collapse:collapse;
    margin-top:12px;
}
table.main th,
table.main td{
    border:1px solid #000;
    padding:6px;
    text-align:center;
}
table.main th{
    background:#f2f2f2;
}

table.breakdown{
    width:100%;
    border-collapse:collapse;
    margin-top:18px;
}
table.breakdown th,
table.breakdown td{
    border:1px solid #000;
    padding:6px;
}
table.breakdown th{
    background:#f2f2f2;
    text-align:left;
}
.right{
    text-align:right !important;
}
.total-box{
    margin-top:12px;
    text-align:right;
    font-size:15px;
    font-weight:bold;
}
.signatures{
    margin-top:35px;
    width:100%;
}
.sign{
    width:45%;
    display:inline-block;
    text-align:center;
}
.sign-line{
    border-top:1px solid #000;
    margin-top:45px;
    padding-top:4px;
}
.note{
    margin-top:12px;
    font-size:11px;
    color:#444;
}
.page-break{
    page-break-after:always;
}
</style>
</head>
<body>

@foreach($rows as $row)

@php
$gross       = $row->gross_amount ?? $row->expected_amount ?? 0;
$discount    = $row->discount_amount ?? 0;
$ar          = $row->ar_balance ?? 0;
$expense     = $row->store_expenses ?? 0;
$delivery    = $row->delivery_fee ?? 0;
$other       = $row->other_income ?? 0;

$otherIncome = $delivery + $other;
$netSales    = ($gross + $otherIncome) - $discount;
$actual      = $row->actual_amount ?? 0;
$variance    = $row->variance ?? 0;

/* CASH ONLY FROM DENOMINATION */
$cashOnly =
($row->denom_1000 * 1000) +
($row->denom_500  * 500) +
($row->denom_200  * 200) +
($row->denom_100  * 100) +
($row->denom_50   * 50) +
($row->denom_20   * 20) +
($row->coin_10    * 10) +
($row->coin_5     * 5) +
($row->coin_1     * 1);

/* FINAL RECONCILIATION */
$finalVariance = $netSales - ($cashOnly + $ar + $expense);
@endphp

<div class="box">

    <div class="header">
        <div class="company">NICOLE TILES CENTER</div>
        <div class="sub">CASH DEPOSIT SLIP</div>
    </div>

    <table class="info-table">
        <tr>
            <td width="60%">
                <strong>Branch:</strong>
                <span class="field-line">{{ $row->branch_name ?? '-' }}</span>
            </td>
            <td width="40%">
                <strong>Date:</strong>
                <span class="field-line small">{{ $row->deposit_date }}</span>
            </td>
        </tr>

        <tr>
            <td colspan="2">
                <strong>Deposited By:</strong>
                <span class="field-line">{{ auth()->user()->name }}</span>
            </td>
        </tr>
    </table>

    <table class="main">
        <thead>
            <tr>
                <th>Denomination</th>
                <th>Qty</th>
                <th>Amount</th>
            </tr>
        </thead>

        <tbody>
            <tr><td>1000</td><td>{{ $row->denom_1000 }}</td><td>{{ number_format($row->denom_1000 * 1000,2) }}</td></tr>
            <tr><td>500</td><td>{{ $row->denom_500 }}</td><td>{{ number_format($row->denom_500 * 500,2) }}</td></tr>
            <tr><td>200</td><td>{{ $row->denom_200 }}</td><td>{{ number_format($row->denom_200 * 200,2) }}</td></tr>
            <tr><td>100</td><td>{{ $row->denom_100 }}</td><td>{{ number_format($row->denom_100 * 100,2) }}</td></tr>
            <tr><td>50</td><td>{{ $row->denom_50 }}</td><td>{{ number_format($row->denom_50 * 50,2) }}</td></tr>
            <tr><td>20</td><td>{{ $row->denom_20 }}</td><td>{{ number_format($row->denom_20 * 20,2) }}</td></tr>
            <tr><td>10 Coin</td><td>{{ $row->coin_10 }}</td><td>{{ number_format($row->coin_10 * 10,2) }}</td></tr>
            <tr><td>5 Coin</td><td>{{ $row->coin_5 }}</td><td>{{ number_format($row->coin_5 * 5,2) }}</td></tr>
            <tr><td>1 Coin</td><td>{{ $row->coin_1 }}</td><td>{{ number_format($row->coin_1 * 1,2) }}</td></tr>
        </tbody>
    </table>

    <div class="total-box">
        TOTAL CASH DEPOSIT: PHP {{ number_format($cashOnly,2) }}
    </div>

    <table class="breakdown">
        <tr>
            <th width="65%">Deposit Breakdown</th>
            <th width="35%">Amount</th>
        </tr>

        <tr>
            <td>Gross Sales</td>
            <td class="right">{{ number_format($gross,2) }}</td>
        </tr>

        <tr>
            <td>Delivery Fee</td>
            <td class="right">{{ number_format($delivery,2) }}</td>
        </tr>

        <tr>
            <td>Other Income</td>
            <td class="right">{{ number_format($other,2) }}</td>
        </tr>

        <tr>
            <td>Less Discount</td>
            <td class="right">{{ number_format($discount,2) }}</td>
        </tr>

        <tr>
            <td><strong>Net Sales</strong></td>
            <td class="right"><strong>{{ number_format($netSales,2) }}</strong></td>
        </tr>

        <tr>
            <td>Cash Deposit</td>
            <td class="right">{{ number_format($cashOnly,2) }}</td>
        </tr>

        <tr>
            <td>A/R Balance</td>
            <td class="right">{{ number_format($ar,2) }}</td>
        </tr>

        <tr>
            <td>Store Expenses</td>
            <td class="right">{{ number_format($expense,2) }}</td>
        </tr>

        <tr>
            <td><strong>Variance</strong></td>
            <td class="right"><strong>{{ number_format($finalVariance,2) }}</strong></td>
        </tr>
    </table>

    <div class="signatures">
        <div class="sign">
            <div class="sign-line">Depositor Signature</div>
        </div>

        <div class="sign" style="float:right;">
            <div class="sign-line">Received By</div>
        </div>
    </div>

    <div class="note">
        Generated Deposit Slip Report
    </div>

</div>

@if(!$loop->last)
<div class="page-break"></div>
@endif

@endforeach

</body>
</html>