@php
$layout = match(strtolower(auth()->user()->role)) {
    'admin'   => 'layouts.admin',
    'manager' => 'layouts.manager',
    'audit'   => 'layouts.manager',
    default   => 'layouts.cashier',
};
@endphp

@extends($layout)

@section('content')
<style>
.page{max-width:1100px;margin:auto;}
.card{
background:#dbeafe;
border:2px solid #93c5fd;
border-radius:14px;
padding:25px;
box-shadow:0 10px 25px rgba(0,0,0,.08);
}
.title{text-align:center;font-size:28px;font-weight:900;}
.sub{text-align:center;font-size:12px;color:#475569;margin-bottom:15px;}
.grid{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
gap:12px;
}
.input{
width:100%;
padding:10px 12px;
border:1px solid #334155;
border-radius:8px;
background:#fff;
font-size:14px;
}
textarea.input{height:100px;}
.btn{
border:none;
padding:11px 16px;
border-radius:10px;
cursor:pointer;
font-weight:700;
text-decoration:none;
display:inline-block;
text-align:center;
}
.btn-blue{background:#2563eb;color:#fff;}
.btn-green{background:#16a34a;color:#fff;}
.btn-red{background:#dc2626;color:#fff;}
.btn-orange{background:#ea580c;color:#fff;}
.actions{
display:flex;
gap:10px;
flex-wrap:wrap;
margin-top:15px;
}
.box{
margin-top:15px;
padding:14px;
border:1px solid #334155;
border-radius:10px;
background:#fff;
}
.total{
font-size:26px;
font-weight:900;
color:#0f172a;
}
.note{
font-size:12px;
color:#64748b;
margin-top:4px;
}
.alert-success{
background:#dcfce7;color:#166534;padding:12px;
border-radius:8px;margin-bottom:15px;
}
.alert-error{
background:#fee2e2;color:#991b1b;padding:12px;
border-radius:8px;margin-bottom:15px;
}
.badge{
display:inline-block;
padding:6px 10px;
border-radius:999px;
font-size:12px;
font-weight:700;
}
.saved{background:#dcfce7;color:#166534;}
.cancelled{background:#fee2e2;color:#991b1b;}
.returned{background:#ffedd5;color:#9a3412;}
</style>

<div class="page">
<div class="card">

<form method="POST" action="{{ route('cashier.delivery.store') }}">
@csrf

@if(session('success'))
<div class="alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
<div class="alert-error">{{ session('error') }}</div>
@endif

<div class="title">DELIVERY FEE</div>
<div class="sub">Connected to Sales Receipt • Separate Income</div>

<div class="grid">
<input type="text" name="receipt_no" id="receiptNo" class="input" placeholder="Receipt No">

<button type="button" class="btn btn-blue" onclick="loadReceipt()">
🔍 Load Receipt
</button>

<input type="text" name="delivery_no" class="input"
placeholder="Delivery No"
value="{{ $deliveryNo }}">

<input type="date" name="delivery_date" class="input"
value="{{ date('Y-m-d') }}">
</div>

<div class="grid" style="margin-top:12px;">
<input type="text" name="customer_name" id="customerName"
class="input" placeholder="Customer Name">

<input type="text" name="address" id="address"
class="input" placeholder="Address">

<input type="text" name="rider_name"
class="input" placeholder="Rider / Driver Name">

<input type="number" step="0.01" name="amount"
class="input" placeholder="Delivery Fee Amount">
</div>

<div style="margin-top:12px;">
<textarea name="notes" class="input"
placeholder="Notes / Instructions"></textarea>
</div>

<input type="hidden" name="status" id="statusField" value="saved">

<div class="actions">
<button type="submit" class="btn btn-green"
onclick="setStatus('saved')">
💾 Save Delivery
</button>

<button type="submit" class="btn btn-red"
onclick="setStatus('cancelled')">
❌ Cancel
</button>

<button type="submit" class="btn btn-orange"
onclick="setStatus('returned')">
↩ Return
</button>

<button type="button"
class="btn btn-blue"
onclick="window.location.href='{{ route('cashier.delivery.today') }}'">
📋 Delivery List
</button>
</div>

<div class="box">
<div>Current Status:
<span id="statusBadge" class="badge saved">saved</span>
</div>

<div style="margin-top:12px;">Today Delivery Income</div>
<div class="total">
₱{{ number_format(
DB::table('delivery_fees')
->whereDate('delivery_date', date('Y-m-d'))
->where('branch_id', auth()->user()->branch_id)
->where('status', 'saved')
->sum('amount')
,2) }}
</div>

<div class="note">
Saved = income • Cancelled = void • Returned = refund
</div>
</div>

</form>

</div>
</div>

<script>
function setStatus(status){
document.getElementById('statusField').value = status;

let badge = document.getElementById('statusBadge');
badge.className = 'badge ' + status;
badge.innerText = status;
}

function loadReceipt(){
let receipt = document.getElementById('receiptNo').value.trim();

if(receipt === ''){
alert('Enter receipt number first.');
return;
}

fetch('/cashier/delivery/load/' + receipt)
.then(response => response.json())
.then(data => {

    if(!data.success){
        alert(data.message || 'Receipt not found.');
        return;
    }

    document.getElementById('customerName').value =
        data.customer_name ?? '';

    document.getElementById('address').value =
        data.address ?? '';

})
.catch(error => {
    console.log(error);
    alert('Unable to load receipt.');
});
}
</script>

@endsection