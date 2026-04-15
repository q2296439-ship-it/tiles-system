@extends('layouts.cashier')

@section('content')

<style>
.page{
    max-width:1200px;
    margin:auto;
}
.receipt-card{
    background:#f8d7df;
    border:2px solid #d8aab4;
    border-radius:14px;
    padding:25px;
    box-shadow:0 10px 25px rgba(0,0,0,.08);
}
.head-title{
    text-align:center;
    font-size:28px;
    font-weight:900;
    letter-spacing:1px;
    color:#3b2430;
    margin-bottom:2px;
}
.head-sub{
    text-align:center;
    font-size:12px;
    color:#4b5563;
    margin-bottom:2px;
}
.head-mini{
    text-align:center;
    font-size:11px;
    color:#4b5563;
    margin-bottom:15px;
}
.invoice-row{
    display:flex;
    justify-content:space-between;
    gap:20px;
    flex-wrap:wrap;
    margin-bottom:15px;
}
.label-box{
    border:1px solid #333;
    padding:6px 10px;
    font-weight:700;
    background:#fff;
    display:inline-block;
}
.form-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:12px;
    margin-bottom:15px;
}
.input{
    width:100%;
    padding:10px 12px;
    border:1px solid #444;
    background:#fff;
    border-radius:6px;
    font-size:14px;
}
.table-wrap{
    overflow:auto;
    margin-top:10px;
}
table{
    width:100%;
    border-collapse:collapse;
    background:#fff;
}
th,td{
    border:1px solid #333;
    padding:8px;
    font-size:14px;
    text-align:left;
}
th{
    background:#f3f4f6;
    font-weight:700;
}
.center{ text-align:center; }
.right{ text-align:right; }

.btn{
    border:none;
    padding:11px 16px;
    border-radius:10px;
    cursor:pointer;
    font-weight:700;
}
.btn-blue{ background:#2563eb; color:#fff; }
.btn-green{ background:#16a34a; color:#fff; }
.btn-red{ background:#dc2626; color:#fff; }
.btn-disabled{
    background:#9ca3af !important;
    color:#fff !important;
    cursor:not-allowed !important;
}

.actions{
    display:flex;
    gap:10px;
    margin-top:15px;
    flex-wrap:wrap;
}
.footer-box{
    margin-top:15px;
    display:grid;
    grid-template-columns:1fr 320px;
    gap:15px;
}
.note-box{
    border:1px solid #333;
    min-height:120px;
    background:#fff;
    padding:10px;
}
.total-box{
    border:1px solid #333;
    background:#fff;
}
.total-line{
    display:flex;
    justify-content:space-between;
    padding:10px 12px;
    border-bottom:1px solid #ddd;
    font-size:14px;
    gap:10px;
    align-items:center;
}
.total-final{
    font-size:22px;
    font-weight:900;
    color:#111827;
}
.signature{
    margin-top:18px;
    display:flex;
    justify-content:flex-end;
}
.sign-box{
    width:280px;
    text-align:center;
    font-size:13px;
}
.line{
    border-top:1px solid #111;
    margin-bottom:6px;
}
.alert-success{
    background:#dcfce7;
    color:#166534;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
    font-weight:600;
}
.alert-error{
    background:#fee2e2;
    color:#991b1b;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
    font-weight:600;
}
.alert-warning{
    background:#fef3c7;
    color:#92400e;
    padding:12px;
    border-radius:8px;
    margin-bottom:15px;
    font-weight:700;
}
.small-input{
    width:110px;
    padding:6px 8px;
    border:1px solid #ccc;
    border-radius:6px;
}
</style>

@php
$todayClosed = \App\Models\Deposit::whereDate('deposit_date', date('Y-m-d'))
    ->where('branch_id', auth()->user()->branch_id)
    ->exists();
@endphp

<div class="page">
<div class="receipt-card">

<form method="POST" action="{{ route('cashier.collection.store') }}" autocomplete="off">
@csrf

@if(session('success'))
<div class="alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert-error">{{ session('error') }}</div>
@endif

@if ($errors->any())
<div class="alert-error">
    @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
    @endforeach
</div>
@endif

@if($todayClosed)
<div class="alert-warning">
    🔒 Today's transaction is already deposited and closed. Saving new receipt is disabled.
</div>
@endif

<div class="head-title">NMC HOME IMPROVEMENT CENTER</div>
<div class="head-sub">Brgy. Virgen Delos Remedios, Angeles City, Pampanga</div>
<div class="head-mini">NON-VAT REG TIN: 489-000-696-004</div>

<div class="invoice-row">
    <div class="label-box">SALES INVOICE</div>

    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <strong>No.</strong>
        <input type="text" name="receipt_no" class="input" style="width:140px;" required>

        <strong>Date</strong>
        <input type="date" name="receipt_date" class="input" value="{{ date('Y-m-d') }}" style="width:170px;" required>
    </div>
</div>

<div class="form-grid">
    <input type="text" name="customer_name" class="input" placeholder="SOLD TO">
    <input type="text" name="terms" class="input" placeholder="Terms">
    <input type="text" name="tin" class="input" placeholder="TIN / SC-TIN">
    <input type="text" name="pwd_id" class="input" placeholder="OSCA/PWD ID No.">
    <input type="text" name="business_style" class="input" placeholder="Business Style">
    <input type="text" name="address" class="input" placeholder="Address">
</div>

<div class="table-wrap">
<table id="itemsTable">
<thead>
<tr>
    <th class="center">Qty</th>
    <th class="center">Unit</th>
    <th>Articles</th>
    <th class="right">Unit Price</th>
    <th class="right">Amount</th>
    <th></th>
</tr>
</thead>
<tbody>
<tr>
    <td><input type="number" name="items[0][qty]" class="input qty" value="1" min="1"></td>
    <td><input type="text" name="items[0][unit]" class="input"></td>
    <td><input type="text" name="items[0][description]" class="input" required></td>
    <td><input type="number" step="0.01" name="items[0][unit_price]" class="input price" value="0"></td>
    <td><input type="number" step="0.01" name="items[0][amount]" class="input amount" value="0" readonly></td>
    <td><button type="button" class="btn btn-red" onclick="removeRow(this)">✖</button></td>
</tr>
</tbody>
</table>
</div>

<div class="actions">
    <button type="button" class="btn btn-blue" onclick="addRow()">➕ Add Row</button>
</div>

<input type="hidden" name="gross_amount" id="grossAmount">
<input type="hidden" name="discount_amount" id="hiddenDiscount">
<input type="hidden" name="discount_type" id="hiddenDiscountType">
<input type="hidden" name="total_amount" id="totalAmount">

<div class="footer-box">

    <div class="note-box">
        <strong>C.O.D</strong><br><br>

        <label><strong>Discount Type</strong></label><br>
        <select id="discountType" class="small-input">
            <option value="none">None</option>
            <option value="senior">Senior (20%)</option>
            <option value="pwd">PWD (20%)</option>
            <option value="custom">Custom</option>
        </select>

        <br><br>

        <label><strong>Custom Discount</strong></label><br>
        <input type="number" id="discountValue" class="small-input" value="0" min="0" step="0.01">
    </div>

    <div class="total-box">
        <div class="total-line">
            <span>Total Sales</span>
            <span id="salesAmount">₱0.00</span>
        </div>

        <div class="total-line">
            <span>Less SC/PWD Discount</span>
            <span id="discountAmount">₱0.00</span>
        </div>

        <div class="total-line total-final">
            <span>TOTAL AMOUNT DUE</span>
            <span id="grandTotal">₱0.00</span>
        </div>
    </div>

</div>

<div class="signature">
    <div class="sign-box">
        <div class="line"></div>
        Cashier / Authorized Representative
    </div>
</div>

<div class="actions" style="justify-content:flex-end;">
    <button type="submit"
        class="btn {{ $todayClosed ? 'btn-disabled' : 'btn-green' }}"
        style="min-width:180px;"
        {{ $todayClosed ? 'disabled' : '' }}>
        💾 Save Receipt
    </button>
</div>

</form>

</div>
</div>

<script>
let rowIndex = 1;

function addRow(){
    let body = document.querySelector('#itemsTable tbody');

    body.insertAdjacentHTML('beforeend', `
        <tr>
            <td><input type="number" name="items[${rowIndex}][qty]" class="input qty" value="1" min="1"></td>
            <td><input type="text" name="items[${rowIndex}][unit]" class="input"></td>
            <td><input type="text" name="items[${rowIndex}][description]" class="input" required></td>
            <td><input type="number" step="0.01" name="items[${rowIndex}][unit_price]" class="input price" value="0"></td>
            <td><input type="number" step="0.01" name="items[${rowIndex}][amount]" class="input amount" value="0" readonly></td>
            <td><button type="button" class="btn btn-red" onclick="removeRow(this)">✖</button></td>
        </tr>
    `);

    rowIndex++;
}

function removeRow(btn){
    btn.closest('tr').remove();
    computeAll();
}

document.addEventListener('input', function(e){
    if(
        e.target.classList.contains('qty') ||
        e.target.classList.contains('price') ||
        e.target.id === 'discountValue'
    ){
        let row = e.target.closest('tr');
        if(row){
            let qty = parseFloat(row.querySelector('.qty').value) || 0;
            let price = parseFloat(row.querySelector('.price').value) || 0;
            row.querySelector('.amount').value = (qty * price).toFixed(2);
        }
        computeAll();
    }
});

document.getElementById('discountType').addEventListener('change', computeAll);

function computeAll(){
    let total = 0;

    document.querySelectorAll('.amount').forEach(item=>{
        total += parseFloat(item.value) || 0;
    });

    let discount = 0;
    let type = document.getElementById('discountType').value;
    let custom = parseFloat(document.getElementById('discountValue').value) || 0;

    if(type === 'senior' || type === 'pwd'){
        discount = total * 0.20;
    }

    if(type === 'custom'){
        discount = custom;
    }

    if(discount > total){
        discount = total;
    }

    let grand = total - discount;

    document.getElementById('salesAmount').innerText = '₱' + total.toFixed(2);
    document.getElementById('discountAmount').innerText = '₱' + discount.toFixed(2);
    document.getElementById('grandTotal').innerText = '₱' + grand.toFixed(2);
    document.getElementById('grossAmount').value = total.toFixed(2);
    document.getElementById('hiddenDiscount').value = discount.toFixed(2);
    document.getElementById('hiddenDiscountType').value = type;
    document.getElementById('totalAmount').value = grand.toFixed(2);
}

computeAll();
</script>

@endsection