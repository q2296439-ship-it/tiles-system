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
th,td{border:1px solid #333;padding:8px;font-size:14px;}
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
.total-final{font-size:22px;font-weight:900;color:#1d4ed8;}
.alert-success{
background:#dcfce7;color:#166534;padding:12px;
border-radius:8px;margin-bottom:15px;
}
.alert-error{
background:#fee2e2;color:#991b1b;padding:12px;
border-radius:8px;margin-bottom:15px;
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
       autocomplete="off"
       required>

<input type="date"
       name="return_date"
       class="input"
       style="width:170px;"
       value="{{ session('success') ? date('Y-m-d') : old('return_date', date('Y-m-d')) }}"
       autocomplete="off"
       required>
</div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:12px;">
<input type="text"
       name="receipt_no"
       class="input"
       placeholder="Original Receipt No"
       value="{{ session('success') ? '' : old('receipt_no') }}"
       autocomplete="off">

<input type="text"
       name="customer_name"
       class="input"
       placeholder="Customer Name"
       value="{{ session('success') ? '' : old('customer_name') }}"
       autocomplete="off">
</div>

<div class="table-wrap">
<table id="itemsTable">
<thead>
<tr>
<th>Qty</th>
<th>Unit</th>
<th>Articles</th>
<th>Price</th>
<th>Amount</th>
<th></th>
</tr>
</thead>
<tbody>
<tr>
<td><input type="number" name="items[0][qty]" class="input qty" value="1" autocomplete="off"></td>
<td><input type="text" name="items[0][unit]" class="input" autocomplete="off"></td>
<td><input type="text" name="items[0][description]" class="input" autocomplete="off"></td>
<td><input type="number" step="0.01" name="items[0][unit_price]" class="input price" value="0" autocomplete="off"></td>
<td><input type="number" step="0.01" name="items[0][amount]" class="input amount" value="0" readonly></td>
<td><button type="button" class="btn btn-red" onclick="removeRow(this)">✖</button></td>
</tr>
</tbody>
</table>
</div>

<div class="actions">
<button type="button" class="btn btn-blue" onclick="addRow()">➕ Add Row</button>
</div>

<input type="hidden" name="total_amount" id="totalAmount">

<div class="footer-box">
<div class="note-box">
<strong>Reason</strong><br><br>
<textarea name="reason"
          class="input"
          style="height:120px;"
          autocomplete="off"
          required>{{ session('success') ? '' : old('reason') }}</textarea>
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

function addRow(){
document.querySelector('#itemsTable tbody').insertAdjacentHTML('beforeend', `
<tr>
<td><input type="number" name="items[${rowIndex}][qty]" class="input qty" value="1" autocomplete="off"></td>
<td><input type="text" name="items[${rowIndex}][unit]" class="input" autocomplete="off"></td>
<td><input type="text" name="items[${rowIndex}][description]" class="input" autocomplete="off"></td>
<td><input type="number" step="0.01" name="items[${rowIndex}][unit_price]" class="input price" value="0" autocomplete="off"></td>
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
if(e.target.classList.contains('qty') || e.target.classList.contains('price')){
let row = e.target.closest('tr');
let qty = parseFloat(row.querySelector('.qty').value)||0;
let price = parseFloat(row.querySelector('.price').value)||0;
row.querySelector('.amount').value = (qty*price).toFixed(2);
computeAll();
}
});

function computeAll(){
let total = 0;
document.querySelectorAll('.amount').forEach(el=>{
total += parseFloat(el.value)||0;
});
document.getElementById('grandTotal').innerText = '₱'+total.toFixed(2);
document.getElementById('totalAmount').value = total.toFixed(2);
}

computeAll();

@if(session('success'))
document.addEventListener('DOMContentLoaded', function () {
document.getElementById('returnForm').reset();
document.querySelector('#itemsTable tbody').innerHTML = `
<tr>
<td><input type="number" name="items[0][qty]" class="input qty" value="1" autocomplete="off"></td>
<td><input type="text" name="items[0][unit]" class="input" autocomplete="off"></td>
<td><input type="text" name="items[0][description]" class="input" autocomplete="off"></td>
<td><input type="number" step="0.01" name="items[0][unit_price]" class="input price" value="0" autocomplete="off"></td>
<td><input type="number" step="0.01" name="items[0][amount]" class="input amount" value="0" readonly></td>
<td><button type="button" class="btn btn-red" onclick="removeRow(this)">✖</button></td>
</tr>
`;
rowIndex = 1;
computeAll();
});
@endif
</script>

@endsection