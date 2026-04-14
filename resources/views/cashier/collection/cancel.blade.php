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
.head-sub,.head-mini{
    text-align:center;
    color:#4b5563;
}
.head-sub{ font-size:12px; }
.head-mini{
    font-size:11px;
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
}
.form-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(240px,1fr));
    gap:12px;
    margin-bottom:15px;
}
.input,.textarea{
    width:100%;
    padding:10px 12px;
    border:1px solid #444;
    background:#fff;
    border-radius:6px;
    font-size:14px;
}
.textarea{
    min-height:130px;
    resize:vertical;
}
.footer-box{
    margin-top:15px;
    display:grid;
    grid-template-columns:1fr 320px;
    gap:15px;
}
.note-box,.total-box{
    border:1px solid #333;
    background:#fff;
    padding:12px;
}
.total-line{
    display:flex;
    justify-content:space-between;
    padding:10px 0;
    border-bottom:1px solid #ddd;
    font-size:14px;
}
.total-final{
    font-size:22px;
    font-weight:900;
    color:#dc2626;
}
.actions{
    display:flex;
    gap:10px;
    margin-top:15px;
    justify-content:flex-end;
    flex-wrap:wrap;
}
.btn{
    border:none;
    padding:11px 16px;
    border-radius:10px;
    cursor:pointer;
    font-weight:700;
    text-decoration:none;
}
.btn-red{ background:#dc2626; color:#fff; }
.btn-gray{ background:#64748b; color:#fff; }

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
</style>

<div class="page">
<div class="receipt-card">

<form method="POST"
      action="{{ route('cashier.collection.cancel.store') }}"
      autocomplete="off"
      id="cancelForm">
@csrf

@if(session('success'))
<div class="alert-success">{{ session('success') }}</div>
@endif

@if ($errors->any())
<div class="alert-error">
    @foreach ($errors->all() as $error)
        <div>{{ $error }}</div>
    @endforeach
</div>
@endif

<div class="head-title">NMC HOME IMPROVEMENT CENTER</div>
<div class="head-sub">Brgy. Virgen Delos Remedios, Angeles City, Pampanga</div>
<div class="head-mini">NON-VAT REG TIN: 489-000-696-004</div>

<div class="invoice-row">
    <div class="label-box">❌ CANCEL RECEIPT</div>

    <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <strong>No.</strong>
        <input type="text"
               name="receipt_no"
               class="input"
               style="width:140px;"
               value="{{ session('success') ? '' : old('receipt_no') }}"
               autocomplete="off"
               required>

        <strong>Date</strong>
        <input type="date"
               name="receipt_date"
               class="input"
               style="width:170px;"
               value="{{ session('success') ? date('Y-m-d') : old('receipt_date', date('Y-m-d')) }}"
               autocomplete="off"
               required>
    </div>
</div>

<div class="form-grid">
    <input type="text"
           name="customer_name"
           class="input"
           placeholder="SOLD TO"
           value="{{ session('success') ? '' : old('customer_name') }}"
           autocomplete="off">

    <input type="text"
           class="input"
           value="CANCELLED"
           readonly>

    <input type="text"
           class="input"
           value="₱0.00"
           readonly>

    <input type="text"
           class="input"
           value="{{ auth()->user()->name ?? 'Cashier' }}"
           readonly>
</div>

<div class="footer-box">

    <div class="note-box">
        <strong>Reason for Cancellation</strong>
        <br><br>

        <textarea name="cancel_reason"
                  class="textarea"
                  autocomplete="off"
                  required>{{ session('success') ? '' : old('cancel_reason') }}</textarea>
    </div>

    <div class="total-box">
        <div class="total-line">
            <span>Status</span>
            <span><strong>CANCELLED</strong></span>
        </div>

        <div class="total-line">
            <span>Amount</span>
            <span>₱0.00</span>
        </div>

        <div class="total-line total-final" style="border-bottom:none;">
            <span>AUDIT ENTRY</span>
            <span>YES</span>
        </div>
    </div>

</div>

<div class="actions">
    <a href="{{ route('cashier.collection.today') }}" class="btn btn-gray">
        Back
    </a>

    <button type="submit" class="btn btn-red">
        Save Cancel Receipt
    </button>
</div>

</form>

</div>
</div>

@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('cancelForm').reset();
});
</script>
@endif

@endsection