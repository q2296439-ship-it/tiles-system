@extends('layouts.cashier')

@section('content')

<style>
.page{max-width:1300px;margin:auto;}
.grid{display:grid;grid-template-columns:420px 1fr;gap:20px;}
.card{
    background:#fff;
    border-radius:18px;
    padding:22px;
    box-shadow:0 8px 20px rgba(0,0,0,.06);
}
.title{font-size:34px;font-weight:900;color:#0f172a;}
.sub{color:#64748b;margin-top:4px;}
.row{display:flex;gap:10px;align-items:center;flex-wrap:wrap;}
.input{
    width:100%;
    padding:10px 12px;
    border:1px solid #cbd5e1;
    border-radius:10px;
}
.btn{
    border:none;
    padding:10px 14px;
    border-radius:10px;
    color:#fff;
    font-weight:700;
    cursor:pointer;
}
.btn-blue{background:#2563eb;}
.btn-green{background:#16a34a;}

table{width:100%;border-collapse:collapse;}
th,td{
    padding:10px;
    border-bottom:1px solid #e5e7eb;
    text-align:left;
    font-size:14px;
}
th{background:#f8fafc;color:#475569;}
.right{text-align:right;}

.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
    margin-top:18px;
}
.value{font-size:30px;font-weight:900;}
.green{color:#16a34a;}
.red{color:#dc2626;}
.blue{color:#2563eb;}
.small{font-size:13px;color:#64748b;}
</style>

<div class="page">

<form method="POST" action="{{ route('cashier.deposit.store') }}">
@csrf

<div class="card" style="margin-bottom:20px;">
    <div class="row" style="justify-content:space-between;">
        <div>
            <div class="title">🏦 Deposit Report</div>
            <div class="sub">Cash denomination deposit reconciliation</div>
        </div>

        <div class="row">
            <input type="date" name="deposit_date" class="input"
                   value="{{ $selectedDate ?? date('Y-m-d') }}"
                   style="width:180px;">

            <button class="btn btn-green">💾 Save Deposit</button>
        </div>
    </div>
</div>

<div class="grid">

<div class="card">

    <h3 style="margin-top:0;">Cash Denomination</h3>

    <table>
        <tr><th>Bill</th><th>Qty</th><th>Total</th></tr>

        <tr>
            <td>1000</td>
            <td><input type="number" name="denom_1000" class="input denom" data-val="1000" value="0"></td>
            <td class="right lineTotal">0.00</td>
        </tr>

        <tr>
            <td>500</td>
            <td><input type="number" name="denom_500" class="input denom" data-val="500" value="0"></td>
            <td class="right lineTotal">0.00</td>
        </tr>

        <tr>
            <td>200</td>
            <td><input type="number" name="denom_200" class="input denom" data-val="200" value="0"></td>
            <td class="right lineTotal">0.00</td>
        </tr>

        <tr>
            <td>100</td>
            <td><input type="number" name="denom_100" class="input denom" data-val="100" value="0"></td>
            <td class="right lineTotal">0.00</td>
        </tr>

        <tr>
            <td>50</td>
            <td><input type="number" name="denom_50" class="input denom" data-val="50" value="0"></td>
            <td class="right lineTotal">0.00</td>
        </tr>

        <tr>
            <td>20</td>
            <td><input type="number" name="denom_20" class="input denom" data-val="20" value="0"></td>
            <td class="right lineTotal">0.00</td>
        </tr>

        <tr>
            <td>10 Coin</td>
            <td><input type="number" name="coin_10" class="input denom" data-val="10" value="0"></td>
            <td class="right lineTotal">0.00</td>
        </tr>

        <tr>
            <td>5 Coin</td>
            <td><input type="number" name="coin_5" class="input denom" data-val="5" value="0"></td>
            <td class="right lineTotal">0.00</td>
        </tr>

        <tr>
            <td>1 Coin</td>
            <td><input type="number" name="coin_1" class="input denom" data-val="1" value="0"></td>
            <td class="right lineTotal">0.00</td>
        </tr>
    </table>

    <div style="margin-top:15px;">
        <label class="small">Remarks</label>
        <textarea name="remarks" class="input" style="min-height:90px;"></textarea>
    </div>

</div>

<div>

<div class="stats">

<div class="card">
    <div class="small">Gross Collection</div>
    <div class="value">₱{{ number_format($gross ?? 0,2) }}</div>
</div>

<div class="card">
    <div class="small">Discount</div>
    <div class="value blue">₱{{ number_format($discount ?? 0,2) }}</div>
</div>

<div class="card">
    <div class="small">Expected Deposit</div>
    <div class="value green">₱{{ number_format($net ?? 0,2) }}</div>
</div>

<div class="card">
    <div class="small">Actual Deposit</div>
    <div class="value" id="actualText">₱0.00</div>
</div>

<div class="card">
    <div class="small">Variance</div>
    <div class="value red" id="varianceText">
        ₱{{ number_format(0 - ($net ?? 0),2) }}
    </div>
</div>

</div>

<input type="hidden" name="expected_amount" value="{{ $net ?? 0 }}">
<input type="hidden" name="actual_amount" id="actualAmount" value="0">
<input type="hidden" name="variance" id="varianceInput" value="{{ 0 - ($net ?? 0) }}">

</div>

</div>

</form>

</div>

<script>
function computeDeposit(){
    let total = 0;

    document.querySelectorAll('.denom').forEach((input,index)=>{
        let qty = parseFloat(input.value) || 0;
        let val = parseFloat(input.dataset.val) || 0;
        let line = qty * val;

        input.closest('tr').querySelector('.lineTotal').innerText = line.toFixed(2);
        total += line;
    });

    let expected = parseFloat('{{ $net ?? 0 }}');
    let variance = total - expected;

    document.getElementById('actualText').innerText = '₱' + total.toFixed(2);
    document.getElementById('varianceText').innerText = '₱' + variance.toFixed(2);

    document.getElementById('actualAmount').value = total.toFixed(2);
    document.getElementById('varianceInput').value = variance.toFixed(2);
}

document.addEventListener('input', function(e){
    if(e.target.classList.contains('denom')){
        computeDeposit();
    }
});

computeDeposit();
</script>

@endsection