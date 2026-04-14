@extends('layouts.cashier')

@section('content')

<style>
.page{
    max-width:1200px;
    margin:auto;
}

.card{
    background:#fff;
    border-radius:16px;
    padding:24px;
    box-shadow:0 8px 20px rgba(0,0,0,.05);
}

.title{
    font-size:22px;
    font-weight:800;
    margin-bottom:20px;
    color:#111827;
}

.grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
    margin-bottom:20px;
}

.input{
    width:100%;
    padding:12px 14px;
    border:1px solid #d1d5db;
    border-radius:10px;
    font-size:14px;
}

.input:focus{
    outline:none;
    border-color:#3b82f6;
    box-shadow:0 0 0 3px rgba(59,130,246,.1);
}

.table-wrap{
    overflow:auto;
    margin-top:10px;
}

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
    background:#f9fafb;
}

.btn{
    border:none;
    padding:11px 16px;
    border-radius:10px;
    cursor:pointer;
    font-weight:700;
}

.btn-blue{
    background:#2563eb;
    color:#fff;
}

.btn-green{
    background:#16a34a;
    color:#fff;
}

.btn-red{
    background:#dc2626;
    color:#fff;
}

.total-box{
    text-align:right;
    margin-top:18px;
    font-size:22px;
    font-weight:800;
}

.actions{
    display:flex;
    gap:10px;
    justify-content:flex-end;
    margin-top:18px;
}
</style>

<div class="page">
<div class="card">

<div class="title">🧾 Add Collection Receipt</div>

@if(session('success'))
    <div style="background:#dcfce7;padding:12px;border-radius:10px;margin-bottom:15px;">
        {{ session('success') }}
    </div>
@endif

<form method="POST" action="{{ route('cashier.collection.store') }}">
@csrf

<div class="grid">
    <input type="text" name="receipt_no" class="input" placeholder="Receipt No." required>
    <input type="date" name="receipt_date" class="input" value="{{ date('Y-m-d') }}" required>
    <input type="text" name="customer_name" class="input" placeholder="Customer Name">
    <input type="text" name="address" class="input" placeholder="Address">
    <input type="text" name="terms" class="input" placeholder="Terms">
</div>

<div class="table-wrap">
<table id="itemsTable">
    <thead>
        <tr>
            <th>Qty</th>
            <th>Unit</th>
            <th>Description</th>
            <th>Unit Price</th>
            <th>Amount</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><input type="number" name="items[0][qty]" class="input qty" value="1"></td>
            <td><input type="text" name="items[0][unit]" class="input"></td>
            <td><input type="text" name="items[0][description]" class="input" required></td>
            <td><input type="number" step="0.01" name="items[0][unit_price]" class="input price" value="0"></td>
            <td><input type="number" step="0.01" name="items[0][amount]" class="input amount" value="0" readonly></td>
            <td><button type="button" class="btn btn-red" onclick="removeRow(this)">✖</button></td>
        </tr>
    </tbody>
</table>
</div>

<button type="button" class="btn btn-blue" onclick="addRow()">➕ Add Row</button>

<input type="hidden" name="total_amount" id="totalAmount">

<div class="total-box">
Total: ₱<span id="grandTotal">0.00</span>
</div>

<div class="actions">
    <button type="submit" class="btn btn-green">💾 Save Receipt</button>
</div>

</form>
</div>
</div>

<script>
let rowIndex = 1;

function addRow(){
    let table = document.querySelector('#itemsTable tbody');

    let row = `
    <tr>
        <td><input type="number" name="items[${rowIndex}][qty]" class="input qty" value="1"></td>
        <td><input type="text" name="items[${rowIndex}][unit]" class="input"></td>
        <td><input type="text" name="items[${rowIndex}][description]" class="input" required></td>
        <td><input type="number" step="0.01" name="items[${rowIndex}][unit_price]" class="input price" value="0"></td>
        <td><input type="number" step="0.01" name="items[${rowIndex}][amount]" class="input amount" value="0" readonly></td>
        <td><button type="button" class="btn btn-red" onclick="removeRow(this)">✖</button></td>
    </tr>
    `;
    table.insertAdjacentHTML('beforeend', row);
    rowIndex++;
}

function removeRow(btn){
    btn.closest('tr').remove();
    computeAll();
}

document.addEventListener('input', function(e){
    if(e.target.classList.contains('qty') || e.target.classList.contains('price')){
        let row = e.target.closest('tr');
        let qty = parseFloat(row.querySelector('.qty').value) || 0;
        let price = parseFloat(row.querySelector('.price').value) || 0;
        row.querySelector('.amount').value = (qty * price).toFixed(2);
        computeAll();
    }
});

function computeAll(){
    let total = 0;
    document.querySelectorAll('.amount').forEach(el=>{
        total += parseFloat(el.value) || 0;
    });

    document.getElementById('grandTotal').innerText = total.toFixed(2);
    document.getElementById('totalAmount').value = total.toFixed(2);
}

computeAll();
</script>

@endsection