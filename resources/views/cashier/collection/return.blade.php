@extends('layouts.cashier')

@section('content')

<style>
.page{max-width:1200px;margin:auto;}
.receipt-card{
background:#dbeafe;
border:2px solid #93c5fd;
border-radius:14px;
padding:25px;
box-shadow:0 10px 25px rgba(0,0,0,.08);
}
.head-title{text-align:center;font-size:28px;font-weight:900;}
.head-sub,.head-mini{text-align:center;color:#475569;}
.head-sub{font-size:12px;}
.head-mini{font-size:11px;margin-bottom:15px;}
.invoice-row{
display:flex;justify-content:space-between;gap:20px;
flex-wrap:wrap;margin-bottom:15px;
}
.label-box{
border:1px solid #333;padding:6px 10px;
font-weight:700;background:#fff;
}
.input{
width:100%;padding:10px 12px;border:1px solid #444;
background:#fff;border-radius:6px;font-size:14px;
}
.table-wrap{overflow:auto;margin-top:10px;}
table{width:100%;border-collapse:collapse;background:#fff;}
th,td{border:1px solid #333;padding:8px;font-size:14px;text-align:center;}
th{background:#f3f4f6;}
.btn{
border:none;padding:11px 16px;border-radius:10px;
cursor:pointer;font-weight:700;
}
.btn-blue{background:#2563eb;color:#fff;}
.btn-green{background:#16a34a;color:#fff;}
.btn-red{background:#dc2626;color:#fff;}
.actions{display:flex;gap:10px;margin-top:15px;flex-wrap:wrap;}
.footer-box{
margin-top:15px;
display:grid;
grid-template-columns:1fr 320px;
gap:15px;
}
.note-box,.total-box{
border:1px solid #333;
background:#fff;
padding:10px;
}
.total-line{
display:flex;
justify-content:space-between;
padding:10px 0;
border-bottom:1px solid #ddd;
}
.alert-success{
background:#dcfce7;color:#166534;padding:12px;
border-radius:8px;margin-bottom:15px;
}
.alert-error{
background:#fee2e2;color:#991b1b;padding:12px;
border-radius:8px;margin-bottom:15px;
}
.info-box{
margin-top:12px;
padding:12px;
background:#eff6ff;
border:1px solid #93c5fd;
border-radius:8px;
font-size:13px;
color:#1e3a8a;
}
</style>

<div class="page">
<div class="receipt-card">

<form method="POST"
      action="{{ route('cashier.return.store') }}"
      autocomplete="off"
      id="returnForm">
@csrf

@if(session('success'))
<div class="alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert-error">{{ session('error') }}</div>
@endif

<div class="head-title">NMC HOME IMPROVEMENT CENTER</div>
<div class="head-sub">Return Receipt Entry</div>
<div class="head-mini">Stock will be restored automatically</div>

<div class="invoice-row">
<div class="label-box">↩ RETURN RECEIPT</div>

<div style="display:flex;gap:10px;flex-wrap:wrap;">
<input type="text"
       name="return_no"
       class="input"
       style="width:140px;"
       placeholder="Return No"
       value="{{ session('success') ? '' : old('return_no') }}"
       required>

<input type="date"
       name="return_date"
       class="input"
       style="width:170px;"
       value="{{ session('success') ? date('Y-m-d') : old('return_date', date('Y-m-d')) }}"
       required>
</div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;">
<input type="text"
       name="receipt_no"
       id="receiptNo"
       class="input"
       placeholder="Original Receipt No"
       value="{{ session('success') ? '' : old('receipt_no') }}">

<input type="text"
       name="customer_name"
       id="customerName"
       class="input"
       placeholder="Customer Name"
       value="{{ session('success') ? '' : old('customer_name') }}">
</div>

<div class="info-box">
Sold = Original Price | Discount = Deducted Amount | Net Paid = Actual Customer Payment | Return = Amount to Refund / Deduct to Sales
</div>

<div class="table-wrap">
<table id="itemsTable">
<thead>
<tr>
<th>Qty</th>
<th>Unit</th>
<th>Articles</th>
<th>Price</th>
<th>Sold</th>
<th>Discount</th>
<th>Net Paid</th>
<th>Return</th>
<th></th>
</tr>
</thead>
<tbody>
<tr>
<td><input type="number" name="items[0][qty]" class="input qty" value="1"></td>
<td><input type="text" name="items[0][unit]" class="input"></td>
<td><input type="text" name="items[0][description]" class="input"></td>
<td><input type="number" step="0.01" name="items[0][unit_price]" class="input price" value="0"></td>
<td><input type="number" step="0.01" name="items[0][amount]" class="input sold" value="0" readonly></td>
<td><input type="number" step="0.01" name="items[0][discount_amount]" class="input discount" value="0"></td>
<td><input type="number" step="0.01" name="items[0][net_amount]" class="input net" value="0" readonly></td>
<td><input type="number" step="0.01" name="items[0][return_amount]" class="input returnAmt" value="0"></td>
<td><button type="button" class="btn btn-red" onclick="removeRow(this)">✖</button></td>
</tr>
</tbody>
</table>
</div>

<div class="actions">
<button type="button" class="btn btn-blue" onclick="addRow()">➕ Add Row</button>
<button type="button" class="btn btn-blue" onclick="loadReceipt()">🔍 Load Receipt</button>
</div>

<input type="hidden" name="total_amount" id="totalAmount">

<div class="footer-box">
<div class="note-box">
<strong>Reason</strong><br><br>
<textarea name="reason" class="input" style="height:120px;" required>{{ session('success') ? '' : old('reason') }}</textarea>
</div>

<div class="total-box">
<div class="total-line">
<span>Total Return</span>
<span id="grandTotal">₱0.00</span>
</div>
</div>
</div>

<div class="actions" style="justify-content:flex-end;">
<button type="submit" class="btn btn-green">💾 Save Return</button>
</div>

</form>

</div>
</div>

<script>
let rowIndex = 1;

function rowHtml(i,data={}) {
return `
<tr>
<td><input type="number" name="items[${i}][qty]" class="input qty" value="${data.qty ?? 1}"></td>
<td><input type="text" name="items[${i}][unit]" class="input" value="${data.unit ?? ''}"></td>
<td><input type="text" name="items[${i}][description]" class="input" value="${data.description ?? ''}"></td>
<td><input type="number" step="0.01" name="items[${i}][unit_price]" class="input price" value="${data.unit_price ?? 0}"></td>
<td><input type="number" step="0.01" name="items[${i}][amount]" class="input sold" value="${data.amount ?? 0}" readonly></td>
<td><input type="number" step="0.01" name="items[${i}][discount_amount]" class="input discount" value="${data.discount_amount ?? 0}"></td>
<td><input type="number" step="0.01" name="items[${i}][net_amount]" class="input net" value="${data.net_amount ?? 0}" readonly></td>
<td><input type="number" step="0.01" name="items[${i}][return_amount]" class="input returnAmt" value="${data.return_amount ?? 0}"></td>
<td><button type="button" class="btn btn-red" onclick="removeRow(this)">✖</button></td>
</tr>
`;
}

function addRow(){
document.querySelector('#itemsTable tbody').insertAdjacentHTML('beforeend', rowHtml(rowIndex));
rowIndex++;
computeAll();
}

function removeRow(btn){
btn.closest('tr').remove();
computeAll();
}

function computeRow(row){
let qty = parseFloat(row.querySelector('.qty').value)||0;
let price = parseFloat(row.querySelector('.price').value)||0;
let discount = parseFloat(row.querySelector('.discount').value)||0;

let sold = qty * price;
let net = sold - discount;
if(net < 0) net = 0;

row.querySelector('.sold').value = sold.toFixed(2);
row.querySelector('.net').value = net.toFixed(2);

let ret = row.querySelector('.returnAmt');
if(parseFloat(ret.value) === 0 || parseFloat(ret.value) > net){
ret.value = net.toFixed(2);
}
}

function computeAll(){
let total = 0;

document.querySelectorAll('#itemsTable tbody tr').forEach(row=>{
computeRow(row);
total += parseFloat(row.querySelector('.returnAmt').value)||0;
});

document.getElementById('grandTotal').innerText = '₱'+total.toFixed(2);
document.getElementById('totalAmount').value = total.toFixed(2);
}

document.addEventListener('input', function(e){
if(
e.target.classList.contains('qty') ||
e.target.classList.contains('price') ||
e.target.classList.contains('discount') ||
e.target.classList.contains('returnAmt')
){
computeAll();
}
});

async function loadReceipt(){
let receipt = document.getElementById('receiptNo').value.trim();

if(receipt === ''){
alert('Enter receipt number first.');
return;
}

try{
let res = await fetch("{{ url('/cashier/return/load') }}/" + receipt);
let data = await res.json();

if(!data.success){
alert(data.message);
return;
}

document.getElementById('customerName').value = data.customer_name ?? '';

let tbody = document.querySelector('#itemsTable tbody');
tbody.innerHTML = '';
rowIndex = 0;

data.items.forEach(item=>{
tbody.insertAdjacentHTML('beforeend', rowHtml(rowIndex, item));
rowIndex++;
});

computeAll();

}catch(error){
alert('Unable to load receipt.');
}
}

computeAll();

@if(session('success'))
document.addEventListener('DOMContentLoaded', function(){
document.getElementById('returnForm').reset();
document.querySelector('#itemsTable tbody').innerHTML = rowHtml(0);
rowIndex = 1;
computeAll();
});
@endif
</script>

@endsection