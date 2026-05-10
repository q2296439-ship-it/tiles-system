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
.page-wrap{
    max-width:1280px;
    margin:auto;
    font-family:'Segoe UI',Tahoma,sans-serif;
}

.page-head{
    display:flex;
    justify-content:space-between;
    align-items:flex-start;
    gap:20px;
    flex-wrap:wrap;
    margin-bottom:24px;
}

.page-title{
    font-size:34px;
    font-weight:800;
    margin:0;
    color:#111827;
}

.page-sub{
    color:#6b7280;
    font-size:14px;
    margin-top:6px;
}

.live{
    color:#16a34a;
    font-weight:700;
}

.last-updated{
    background:#fff;
    padding:10px 14px;
    border-radius:12px;
    box-shadow:0 4px 12px rgba(0,0,0,0.05);
    color:#475569;
    font-size:13px;
}

.card{
    background:#fff;
    border-radius:18px;
    padding:22px;
    box-shadow:0 8px 24px rgba(0,0,0,0.05);
}

.filter-card{
    margin-bottom:22px;
}

.filters{
    display:flex;
    gap:12px;
    flex-wrap:wrap;
    align-items:center;
}

input[type="date"],
select{
    padding:11px 14px;
    border:1px solid #d1d5db;
    border-radius:10px;
    font-size:14px;
    background:#fff;
    min-width:160px;
    outline:none;
}

.btn{
    border:none;
    text-decoration:none;
    padding:11px 16px;
    border-radius:10px;
    font-weight:700;
    font-size:14px;
    display:inline-block;
    transition:.2s;
    cursor:pointer;
}

.btn-green{
    background:#16a34a;
    color:#fff;
}

.btn-green:hover{
    background:#15803d;
}

.btn-gray{
    background:#e5e7eb;
    color:#111827;
}

.btn-gray:hover{
    background:#d1d5db;
}

.alert-box{
    background:#fff7ed;
    border-left:5px solid #f59e0b;
    color:#92400e;
    margin-bottom:22px;
}

.alert-box ul{
    margin:10px 0 0 18px;
}

.kpi-grid{
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:18px;
    margin-bottom:22px;
}

.kpi{
    border-radius:18px;
    padding:22px;
    color:#fff;
}

.kpi small{
    display:block;
    opacity:.9;
    font-size:14px;
    margin-bottom:10px;
}

.kpi h2{
    margin:0;
    font-size:32px;
    font-weight:800;
}

.blue{background:linear-gradient(135deg,#3b82f6,#2563eb);}
.green{background:linear-gradient(135deg,#22c55e,#16a34a);}
.orange{background:linear-gradient(135deg,#f59e0b,#d97706);}

.card-title{
    font-size:22px;
    font-weight:800;
    color:#111827;
    margin-bottom:16px;
}

.table-wrap{
    overflow-x:auto;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#f9fafb;
    text-align:left;
    font-size:13px;
    color:#374151;
    font-weight:700;
}

th,td{
    padding:13px;
    border-bottom:1px solid #e5e7eb;
}

tr:hover{
    background:#f9fafb;
}

.right{
    text-align:right;
}

.amount{
    font-weight:700;
    color:#16a34a;
}

.empty{
    text-align:center;
    color:#6b7280;
}

/* PAGINATION */
.pagination-wrap{
    display:flex;
    justify-content:center;
    margin-top:25px;
}

.pagination{
    display:flex;
    align-items:center;
    gap:8px;
    flex-wrap:wrap;
}

.pagination a,
.pagination span{
    min-width:38px;
    height:38px;
    padding:0 12px;
    border-radius:10px;
    display:flex;
    align-items:center;
    justify-content:center;
    text-decoration:none;
    font-size:14px;
    font-weight:700;
    transition:.2s ease;
}

.pagination a{
    background:#f1f5f9;
    color:#0f172a;
    border:1px solid #cbd5e1;
}

.pagination a:hover{
    background:#2563eb;
    color:#fff;
    border-color:#2563eb;
}

.pagination .active span{
    background:#2563eb;
    color:#fff;
    border:1px solid #2563eb;
}

.pagination .disabled span{
    background:#e5e7eb;
    color:#94a3b8;
    cursor:not-allowed;
}

@media (max-width:1100px){
    .kpi-grid{
        grid-template-columns:1fr;
    }
}

@media (max-width:700px){
    .page-title{
        font-size:28px;
    }
}
</style>

<div class="page-wrap">

    <!-- HEADER -->
    <div class="page-head">

        <div>
            <h1 class="page-title">📊 Daily Sales <span class="live">● Live</span></h1>
            <div class="page-sub">
                Monitor real-time sales transactions, trends, and performance.
            </div>
        </div>

        <div class="last-updated" id="lastUpdate">
            Last updated: {{ now()->format('h:i:s A') }}
        </div>

    </div>

    <!-- FILTERS -->
    <div class="card filter-card">
        <form method="GET" class="filters">

            <select name="range" onchange="this.form.submit()">
                <option value="daily" {{ $range=='daily'?'selected':'' }}>Today</option>
                <option value="week" {{ $range=='week'?'selected':'' }}>This Week</option>
                <option value="month" {{ $range=='month'?'selected':'' }}>This Month</option>
            </select>

            <input type="date" name="start_date"
                value="{{ request('start_date') }}">

            <input type="date" name="end_date"
                value="{{ request('end_date') }}">

            <select name="branch_id">
                <option value="">All Branches</option>
                @foreach($branchList as $b)
                    <option value="{{ $b->id }}"
                        {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                        {{ $b->name }}
                    </option>
                @endforeach
            </select>

            <button class="btn btn-green" type="submit">Filter</button>

            <a href="/admin/sales/daily/excel?range={{ $range }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}&branch_id={{ request('branch_id') }}"
               class="btn btn-green export-btn">
               📊 Excel
            </a>

            <a href="/admin/sales/daily/pdf?range={{ $range }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}&branch_id={{ request('branch_id') }}"
               target="_blank"
               class="btn btn-gray export-btn">
               📄 PDF
            </a>

        </form>
    </div>

    @if(isset($alerts) && count($alerts) > 0)
        <div class="card alert-box">
            <strong>⚠ Alerts</strong>
            <ul>
                @foreach($alerts as $alert)
                    <li>{{ $alert }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- KPI -->
    <div class="kpi-grid">

        <div class="kpi blue">
            <small>Total Sales</small>
            <h2 id="totalSales">₱{{ number_format($total, 2) }}</h2>
        </div>

        <div class="kpi green">
            <small>Transactions</small>
            <h2 id="transactionCount">{{ $transactionCount }}</h2>
        </div>

        <div class="kpi orange">
            <small>Average Sale</small>
            <h2 id="averageSale">₱{{ number_format($average, 2) }}</h2>
        </div>

    </div>

    <!-- CHART -->
    <div class="card" style="margin-bottom:22px;">
        <div class="card-title">📈 Sales Trend</div>
        <canvas id="trendChart" height="90"></canvas>
    </div>

    <!-- TABLE -->
    <div class="card">
        <div class="card-title">🧾 Transactions (Real-time)</div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Branch</th>
                        <th>Cashier</th>
                        <th class="right">Amount</th>
                    </tr>
                </thead>

                <tbody id="salesTable">
                    @forelse($sales as $sale)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y h:i:s A') }}</td>
                        <td>{{ $sale->branch->name ?? 'N/A' }}</td>
                        <td>{{ $sale->user->name ?? 'N/A' }}</td>
                        <td class="right amount">₱{{ number_format($sale->total_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="empty">No sales found</td>
                    </tr>
                    @endforelse
                </tbody>
    </table>
</div>

@if($sales->hasPages())
<div class="pagination-wrap">
    {{ $sales->links() }}
</div>
@endif

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let isDownloading = false;

document.querySelectorAll('.export-btn').forEach(link => {
    link.addEventListener('click', () => {
        isDownloading = true;
    });
});

let trendChart = new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: @json($labels),
        datasets: [{
            label: 'Sales',
            data: @json($data),
            fill: true,
            borderWidth: 3,
            tension: .35,
            pointRadius: 4
        }]
    },
    options: {
        responsive:true,
        plugins:{ legend:{ display:false } },
        scales:{ y:{ beginAtZero:true } }
    }
});

// live clock
function updateTime(){
    const now = new Date();
    document.getElementById('lastUpdate').innerText =
        "Last updated: " + now.toLocaleTimeString();
}
updateTime();
setInterval(updateTime,1000);

// silent update
setInterval(() => {

    if(isDownloading) return;

    fetch(window.location.href, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.text())
    .then(html => {

        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');

        // KPI
        const freshTotal = doc.getElementById('totalSales');
        const freshCount = doc.getElementById('transactionCount');
        const freshAvg   = doc.getElementById('averageSale');

        if(freshTotal) document.getElementById('totalSales').innerHTML = freshTotal.innerHTML;
        if(freshCount) document.getElementById('transactionCount').innerHTML = freshCount.innerHTML;
        if(freshAvg)   document.getElementById('averageSale').innerHTML = freshAvg.innerHTML;

        // TABLE
        const freshTable = doc.getElementById('salesTable');
        if(freshTable){
            document.getElementById('salesTable').innerHTML = freshTable.innerHTML;
        }

    });

}, 5000);
</script>

@endsection