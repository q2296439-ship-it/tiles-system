<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Deposit Report</title>

<style>
body{
    font-family: Arial, sans-serif;
    font-size:11px;
    padding:25px;
    color:#111;
}
.topbar{
    height:6px;
    background:#111;
    margin-bottom:12px;
}
.company{
    font-size:18px;
    font-weight:bold;
}
.title{
    font-size:13px;
    color:#555;
}
.date{
    margin-top:8px;
    margin-bottom:15px;
    font-size:11px;
    color:#666;
}
table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    border:1px solid #000;
    padding:5px;
    font-size:10px;
    text-align:center;
}
th{
    background:#111;
    color:#fff;
}
.right{
    text-align:right;
}
.footer{
    margin-top:15px;
    text-align:right;
    font-weight:bold;
}
</style>
</head>

<body>

<div class="topbar"></div>

<div class="company">NICOLE TILES CENTER</div>
<div class="title">Deposit Denomination Report</div>

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
    <th>1000</th>
    <th>500</th>
    <th>200</th>
    <th>100</th>
    <th>50</th>
    <th>20</th>
    <th>10</th>
    <th>5</th>
    <th>1</th>
    <th>Actual</th>
    <th>Variance</th>
</tr>
</thead>

<tbody>
@foreach($rows as $row)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $row->branch_name ?? '-' }}</td>
    <td>{{ $row->deposit_date }}</td>
    <td>{{ $row->denom_1000 }}</td>
    <td>{{ $row->denom_500 }}</td>
    <td>{{ $row->denom_200 }}</td>
    <td>{{ $row->denom_100 }}</td>
    <td>{{ $row->denom_50 }}</td>
    <td>{{ $row->denom_20 }}</td>
    <td>{{ $row->coin_10 }}</td>
    <td>{{ $row->coin_5 }}</td>
    <td>{{ $row->coin_1 }}</td>
    <td class="right">{{ number_format($row->actual_amount,2) }}</td>
    <td class="right">{{ number_format($row->variance,2) }}</td>
</tr>
@endforeach
</tbody>
</table>

<div class="footer">
TOTAL DEPOSIT:
₱{{ number_format($rows->sum('actual_amount'),2) }}
</div>

</body>
</html>