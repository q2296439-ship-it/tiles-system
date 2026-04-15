@extends('layouts.cashier')

@section('content')

<style>
.page{max-width:1200px;margin:auto;}
.header-card,.card,.table-card{
    background:#fff;
    border-radius:18px;
    padding:22px;
    box-shadow:0 8px 20px rgba(0,0,0,.06);
}
.header-card{margin-bottom:20px;}
.title{font-size:34px;font-weight:900;color:#0f172a;}
.sub{color:#64748b;margin-top:4px;}

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
.input{
    padding:10px 14px;
    border-radius:10px;
    border:1px solid #cbd5e1;
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
.btn-red{background:#dc2626;}
.btn-dark{background:#0f172a;}

.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px;
    margin-bottom:20px;
}
.label{font-size:13px;color:#64748b;margin-bottom:8px;}
.value{font-size:34px;font-weight:900;color:#0f172a;}
.green{color:#16a34a;}
.red{color:#dc2626;}
.blue{color:#2563eb;}

table{width:100%;border-collapse:collapse;}
th,td{
    padding:12px;
    border-bottom:1px solid #e5e7eb;
    text-align:left;
    font-size:14px;
}
th{background:#f8fafc;color:#475569;}
.right{text-align:right;}

#depositPanel{display:none;margin-bottom:20px;}
textarea{
    width:100%;
    min-height:90px;
    border:1px solid #cbd5e1;
    border-radius:12px;
    padding:12px;
}
</style>

<div class="page">

<form method="GET" action="{{ route('cashier.deposit') }}">
<div class="header-card">
    <div class="toolbar">

        <div>
            <div class="title">🏦 Deposit Report</div>
            <div class="sub">Daily cash deposit with discount reconciliation</div>
        </div>

        <div class="tools">
            <input type="date" name="date" class="input"
                   value="{{ $selectedDate ?? date('Y-m-d') }}">

            <button class="btn btn-blue">Generate</button>

            <a href="#" class="btn btn-green">📗 Excel</a>
            <a href="#" class="btn btn-red">📄 PDF</a>

            <button type="button" class="btn btn-dark" onclick="toggleDeposit()">
                ➕ Deposit
            </button>
        </div>

    </div>
</div>
</form>

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
        <div class="value" id="actualText">₱0.00</div>
    </div>

    <div class="card">
        <div class="label">Variance</div>
        <div class="value red" id="varianceText">
            ₱{{ number_format(0 - ($net ?? 0),2) }}
        </div>
    </div>

</div>

<form method="POST" action="{{ route('cashier.deposit.store') }}">
@csrf

<input type="hidden" name="deposit_date" value="{{ $selectedDate ?? date('Y-m-d') }}">
<input type="hidden" name="expected_amount" value="{{ $net ?? 0 }}">
<input type="hidden" name="actual_amount" id="actualAmount">
<input type="hidden" name="variance" id="varianceInput">

<div class="table-card" id="depositPanel">

    <h3 style="margin-top:0;">Cash Denomination</h3>

    <table>
        <tr><th>Bill</th><th>Qty</th><th>Total</th></tr>

        <tr><td>1000</td><td><input type="number" name="denom_1000" class="input denom" data-val="1000" value="0"></td><td class="right lineTotal">0.00</td></tr>
        <tr><td>500</td><td><input type="number" name="denom_500" class="input denom" data-val="500" value="0"></td><td class="right lineTotal">0.00</td></tr>
        <tr><td>200</td><td><input type="number" name="denom_200" class="input denom" data-val="200" value="0"></td><td class="right lineTotal">0.00</td></tr>
        <tr><td>100</td><td><input type="number" name="denom_100" class="input denom" data-val="100" value="0"></td><td class="right lineTotal">0.00</td></tr>
        <tr><td>50</td><td><input type="number" name="denom_50" class="input denom" data-val="50" value="0"></td><td class="right lineTotal">0.00</td></tr>
        <tr><td>20</td><td><input type="number" name="denom_20" class="input denom" data-val="20" value="0"></td><td class="right lineTotal">0.00</td></tr>
        <tr><td>10 Coin</td><td><input type="number" name="coin_10" class="input denom" data-val="10" value="0"></td><td class="right lineTotal">0.00</td></tr>
        <tr><td>5 Coin</td><td><input type="number" name="coin_5" class="input denom" data-val="5" value="0"></td><td class="right lineTotal">0.00</td></tr>
        <tr><td>1 Coin</td><td><input type="number" name="coin_1" class="input denom" data-val="1" value="0"></td><td class="right lineTotal">0.00</td></tr>
    </table>

    <div style="margin-top:15px;">
        <label class="label">Remarks</label>
        <textarea name="remarks"></textarea>
    </div>

    <div style="margin-top:15px;text-align:right;">
        <button class="btn btn-green">💾 Save Deposit</button>
    </div>

</div>

</form>

</div>

<script>
function toggleDeposit(){
    let panel = document.getElementById('depositPanel');
    panel.style.display = panel.style.display === 'none' ? 'block' : 'none';
}

function computeDeposit(){
    let total = 0;

    document.querySelectorAll('.denom').forEach(input=>{
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