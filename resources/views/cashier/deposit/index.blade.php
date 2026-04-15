@extends('layouts.cashier')

@section('content')

<style>
.page{
    max-width:1200px;
    margin:auto;
}
.header-card,.card,.table-card{
    background:#ffffff;
    border-radius:18px;
    padding:22px;
    box-shadow:0 8px 20px rgba(0,0,0,.06);
}
.header-card{margin-bottom:20px;}
.title{
    font-size:34px;
    font-weight:900;
    color:#0f172a;
}
.sub{
    color:#64748b;
    margin-top:4px;
}
.toolbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    flex-wrap:wrap;
}
.tools{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
}
.input,.btn{
    padding:10px 14px;
    border-radius:10px;
}
.input{
    border:1px solid #cbd5e1;
}
.btn{
    border:none;
    color:#fff;
    font-weight:700;
    cursor:pointer;
    text-decoration:none;
}
.btn-blue{background:#2563eb;}
.btn-green{background:#16a34a;}
.btn-red{background:#dc2626;}

.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px;
    margin-bottom:20px;
}
.label{
    font-size:13px;
    color:#64748b;
    margin-bottom:8px;
}
.value{
    font-size:34px;
    font-weight:900;
    color:#0f172a;
}
.green{color:#16a34a;}
.red{color:#dc2626;}
.blue{color:#2563eb;}

table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:12px;
    border-bottom:1px solid #e5e7eb;
    text-align:left;
    font-size:14px;
}
th{
    background:#f8fafc;
    color:#475569;
}
textarea{
    width:100%;
    min-height:90px;
    border:1px solid #cbd5e1;
    border-radius:12px;
    padding:12px;
}
.sign-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:20px;
    margin-top:25px;
}
.sign-box{
    padding-top:40px;
    border-top:1px solid #94a3b8;
    text-align:center;
    color:#475569;
}
</style>

<div class="page">

<div class="header-card">
    <div class="toolbar">

        <div>
            <div class="title">🏦 Deposit Report</div>
            <div class="sub">Daily cash deposit with discount reconciliation</div>
        </div>

        <form method="GET" action="" class="tools">
            <input type="date"
                   name="date"
                   class="input"
                   value="{{ request('date', date('Y-m-d')) }}">

            <button class="btn btn-blue">Generate</button>

            <a href="#" class="btn btn-green">📗 Excel</a>
            <a href="#" class="btn btn-red">📄 PDF</a>
        </form>

    </div>
</div>

<div class="stats">

    <div class="card">
        <div class="label">Gross Collection</div>
        <div class="value">₱{{ number_format($gross ?? 0,2) }}</div>
    </div>

    <div class="card">
        <div class="label">Total Discount</div>
        <div class="value blue">₱{{ number_format($discount ?? 0,2) }}</div>
    </div>

    <div class="card">
        <div class="label">Net Collection</div>
        <div class="value green">₱{{ number_format($net ?? 0,2) }}</div>
    </div>

    <div class="card">
        <div class="label">Actual Deposit</div>
        <div class="value">₱{{ number_format($actual ?? 0,2) }}</div>
    </div>

    <div class="card">
        <div class="label">Variance</div>
        <div class="value {{ ($variance ?? 0) < 0 ? 'red' : 'green' }}">
            ₱{{ number_format($variance ?? 0,2) }}
        </div>
    </div>

</div>

<div class="table-card">

    <h3 style="margin-top:0;">Deposit Breakdown</h3>

    <table>
        <tr>
            <th>Description</th>
            <th>Amount</th>
        </tr>
        <tr>
            <td>Gross Collection</td>
            <td>₱{{ number_format($gross ?? 0,2) }}</td>
        </tr>
        <tr>
            <td>Less Discounts</td>
            <td>₱{{ number_format($discount ?? 0,2) }}</td>
        </tr>
        <tr>
            <td><strong>Expected Deposit</strong></td>
            <td><strong>₱{{ number_format($net ?? 0,2) }}</strong></td>
        </tr>
        <tr>
            <td>Actual Cash Deposited</td>
            <td>₱{{ number_format($actual ?? 0,2) }}</td>
        </tr>
        <tr>
            <td><strong>Variance</strong></td>
            <td><strong>₱{{ number_format($variance ?? 0,2) }}</strong></td>
        </tr>
    </table>

    <div style="margin-top:20px;">
        <label class="label">Remarks</label>
        <textarea placeholder="Enter remarks..."></textarea>
    </div>

    <div class="sign-grid">
        <div class="sign-box">Prepared by (Cashier)</div>
        <div class="sign-box">Checked by (Manager)</div>
        <div class="sign-box">Received by</div>
    </div>

</div>

</div>

@endsection