@extends('layouts.cashier')

@section('content')

<style>
.page{
    max-width:1200px;
    margin:auto;
}
.header-card{
    background:#ffffff;
    border-radius:18px;
    padding:22px;
    margin-bottom:20px;
    box-shadow:0 8px 20px rgba(0,0,0,.06);
}
.title{
    font-size:34px;
    font-weight:900;
    color:#0f172a;
}
.sub{
    color:#64748b;
    margin-top:4px;
}
.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px;
    margin-bottom:20px;
}
.card{
    background:#ffffff;
    border-radius:18px;
    padding:20px;
    box-shadow:0 8px 20px rgba(0,0,0,.05);
}
.label{
    font-size:13px;
    color:#64748b;
    margin-bottom:10px;
}
.value{
    font-size:34px;
    font-weight:900;
    color:#0f172a;
}
.value-green{
    color:#16a34a;
}
.table-card{
    background:#ffffff;
    border-radius:18px;
    padding:20px;
    box-shadow:0 8px 20px rgba(0,0,0,.05);
}
.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    flex-wrap:wrap;
    margin-bottom:15px;
}
.search{
    padding:10px 14px;
    border:1px solid #cbd5e1;
    border-radius:10px;
    min-width:260px;
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
    color:#475569;
    background:#f8fafc;
}
.badge{
    background:#dcfce7;
    color:#166534;
    padding:6px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
}
.empty{
    text-align:center;
    padding:35px;
    color:#94a3b8;
}
</style>

<div class="page">

<div class="header-card">
    <div class="title">📊 Collection Today</div>
    <div class="sub">All encoded collection receipts for today</div>
</div>

<div class="stats">
    <div class="card">
        <div class="label">Total Receipts</div>
        <div class="value">{{ $collections->count() }}</div>
    </div>

    <div class="card">
        <div class="label">Total Collection</div>
        <div class="value value-green">₱{{ number_format($total, 2) }}</div>
    </div>
</div>

<div class="table-card">

    <div class="topbar">
        <h3 style="margin:0;">Today Receipt List</h3>
        <input type="text" id="searchInput" class="search" placeholder="🔍 Search customer / receipt no...">
    </div>

    <div style="overflow:auto;">
        <table id="collectionTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Receipt No</th>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>Total</th>
                    <th>Cashier</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
            @forelse($collections as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row->receipt_no }}</td>
                    <td>{{ $row->customer_name }}</td>
                    <td>{{ $row->address }}</td>
                    <td>₱{{ number_format($row->total_amount, 2) }}</td>
                    <td>{{ $row->user->name ?? 'Cashier' }}</td>
                    <td>{{ $row->created_at->format('h:i A') }}</td>
                    <td><span class="badge">Saved</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty">No collection found today.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

</div>

</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function () {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll('#collectionTable tbody tr');

    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
    });
});
</script>

@endsection