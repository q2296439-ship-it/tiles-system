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
.right{
    text-align:right !important;
}
.total-box{
    margin-top:15px;
    text-align:right;
    font-size:15px;
    font-weight:bold;
}
.signatures{
    margin-top:45px;
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
    margin-top:15px;
    font-size:11px;
    color:#444;
}
</style>
</head>
<body>

@foreach($rows as $row)

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
        TOTAL CASH DEPOSIT: PHP {{ number_format($row->actual_amount,2) }}
    </div>

    <div class="signatures">
        <div class="sign">
            <div class="sign-line">Depositor Signature</div>
        </div>

        <div class="sign" style="float:right;">
            <div class="sign-line">Received By</div>
        </div>
    </div>

    <div class="note">
        Variance: PHP {{ number_format($row->variance,2) }}
    </div>

</div>

@endforeach

</body>
</html>