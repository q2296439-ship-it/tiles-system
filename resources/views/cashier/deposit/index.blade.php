@php
$layout = match(auth()->user()->role) {
    'admin' => 'layouts.admin',
    'manager' => 'layouts.manager',
    default => 'layouts.cashier',
};

$isCashier = auth()->user()->role === 'cashier';
@endphp

@extends($layout)

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
.btn-dark{background:#0f172a;}

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
.right{text-align:right;}

/* COMPACT DEPOSIT PANEL */
#depositPanel{
    display:none;
    margin-top:20px;
    padding:18px;
}
#depositPanel h3{
    margin:0 0 12px 0;
    font-size:22px;
}
#depositPanel th,
#depositPanel td{
    padding:8px 10px;
    font-size:14px;
    vertical-align:middle;
}
#depositPanel .input{
    width:110px;
    height:36px;
    padding:6px 10px;
    border-radius:8px;
}
#depositPanel tr td:first-child{
    width:120px;
    font-weight:600;
}
#depositPanel .right{
    width:120px;
    text-align:right;
    font-weight:700;
}
#depositPanel textarea{
    min-height:70px;
    padding:10px;
    font-size:14px;
}
#depositPanel .btn{
    padding:9px 14px;
    border-radius:8px;
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

            @if(!$isCashier)
            <select name="branch_id" class="input">
                <option value="">All Branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}"
                        {{ ($selectedBranch == $branch->id) ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>
            @endif

            <input type="date"
                   name="date"
                   class="input"
                   value="{{ request('date', date('Y-m-d')) }}">

            <button class="btn btn-blue">Generate</button>

            <a href="#" class="btn btn-green">📗 Excel</a>
            <a href="#" class="btn btn-red">📄 PDF</a>

            @if($isCashier)
            <button type="button" class="btn btn-dark" onclick="toggleDeposit()">
                ➕ Deposit
            </button>
            @endif
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
        <div class="value" id="actualText">
            ₱{{ number_format($actual ?? 0,2) }}
        </div>
    </div>

    <div class="card">
        <div class="label">Variance</div>
        <div class="value {{ ($variance ?? 0) < 0 ? 'red' : 'green' }}" id="varianceText">
            ₱{{ number_format($variance ?? 0,2) }}
        </div>
    </div>

</div>

<div class="table-card" style="margin-bottom:20px;">
    @if($isClosed ?? false)
        <div style="font-weight:700;color:#16a34a;">
            ✅ Transaction successfully closed.
        </div>
    @else
        <div style="font-weight:700;color:#d97706;">
            ⚠️ Transaction still not deposited. Please proceed to deposit and close the transaction for the day.
        </div>
    @endif
</div>

<form method="POST" action="{{ route('cashier.deposit.store') }}">
@csrf

<input type="hidden" name="deposit_date" value="{{ request('date', date('Y-m-d')) }}">
<input type="hidden" name="expected_amount" value="{{ $net ?? 0 }}">
<input type="hidden" name="actual_amount" id="actualAmount" value="{{ $actual ?? 0 }}">
<input type="hidden" name="variance" id="varianceInput" value="{{ $variance ?? 0 }}">

<div class="table-card" id="depositPanel">

    <h3>Cash Denomination</h3>

    <table>
        <tr>
            <th>Bill</th>
            <th>Qty</th>
            <th>Total</th>
        </tr>

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
        <textarea name="remarks" placeholder="Enter remarks..."></textarea>
    </div>

    <div style="margin-top:12px;text-align:right;">
        <button class="btn btn-green">💾 Save Deposit</button>
    </div>

</div>

</form>

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
            <td id="actualTable">₱{{ number_format($actual ?? 0,2) }}</td>
        </tr>
        <tr>
            <td><strong>Variance</strong></td>
            <td><strong id="varianceTable">₱{{ number_format($variance ?? 0,2) }}</strong></td>
        </tr>
    </table>

    <div class="sign-grid">
        <div class="sign-box">Prepared by (Cashier)</div>
        <div class="sign-box">Checked by (Manager)</div>
        <div class="sign-box">Received by</div>
    </div>

</div>

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

    document.getElementById('actualTable').innerText = '₱' + total.toFixed(2);
    document.getElementById('varianceTable').innerText = '₱' + variance.toFixed(2);

    document.getElementById('actualAmount').value = total.toFixed(2);
    document.getElementById('varianceInput').value = variance.toFixed(2);
}

document.addEventListener('input', function(e){
    if(e.target.classList.contains('denom')){
        computeDeposit();
    }
});
</script>

@endsection