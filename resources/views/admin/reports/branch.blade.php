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
    grid-template-columns:repeat(5,1fr);
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
    font-size:28px;
    font-weight:800;
}

.blue{background:linear-gradient(135deg,#3b82f6,#2563eb);}
.green{background:linear-gradient(135deg,#22c55e,#16a34a);}
.orange{background:linear-gradient(135deg,#f59e0b,#d97706);}
.red{background:linear-gradient(135deg,#ef4444,#dc2626);}
.purple{background:linear-gradient(135deg,#8b5cf6,#7c3aed);}

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

.top-row{
    background:#dcfce7;
    font-weight:700;
}

.right{
    text-align:right;
}

.empty{
    text-align:center;
    color:#6b7280;
}

.chart-card{
    margin-bottom:22px;
}

@media (max-width:1200px){
    .kpi-grid{
        grid-template-columns:repeat(2,1fr);
    }
}

@media (max-width:700px){
    .kpi-grid{
        grid-template-columns:1fr;
    }

    .page-title{
        font-size:28px;
    }
}
</style>

<div class="page-wrap">

    <!-- HEADER -->
    <div class="page-head">

        <div>
            <h1 class="page-title">📊 Sales per Branch <span class="live">● Live</span></h1>
            <div class="page-sub">
                Compare branch performance, totals, rankings, and trends in real-time.
            </div>
        </div>

        <div class="last-updated" id="lastUpdated">
            Last updated: --
        </div>

    </div>

    <!-- FILTERS -->
    <div class="card filter-card">
        <form method="GET" class="filters">

            <select name="range" onchange="this.form.submit()">
                <option value="today" {{ $range=='today'?'selected':'' }}>Today</option>
                <option value="week" {{ $range=='week'?'selected':'' }}>This Week</option>
                <option value="month" {{ $range=='month'?'selected':'' }}>This Month</option>
            </select>

            <input type="date" name="start_date" value="{{ request('start_date') }}">
            <input type="date" name="end_date" value="{{ request('end_date') }}">

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

            <a href="/admin/sales/branch/excel?range={{ $range }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}&branch_id={{ request('branch_id') }}"
               class="btn btn-green export-btn">
               📊 Excel
            </a>

            <a href="/admin/sales/branch/pdf?range={{ $range }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}&branch_id={{ request('branch_id') }}"
               target="_blank"
               class="btn btn-gray export-btn">
               📄 PDF
            </a>

        </form>
    </div>

    <!-- ALERTS -->
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
            <h2 id="grandTotal">₱{{ number_format($grandTotal, 2) }}</h2>
        </div>

        <div class="kpi green">
            <small>Transactions</small>
            <h2 id="totalTransactions">{{ $totalTransactions }}</h2>
        </div>

        <div class="kpi orange">
            <small>Average Sale</small>
            <h2 id="averageSale">₱{{ number_format($average, 2) }}</h2>
        </div>

        <div class="kpi purple">
            <small>Top Branch</small>
            <h2 id="topBranch">{{ $topBranch->branch_name ?? '-' }}</h2>
        </div>

        <div class="kpi red">
            <small>Lowest Branch</small>
            <h2 id="lowestBranch">{{ $lowestBranch->branch_name ?? '-' }}</h2>
        </div>

    </div>

    <!-- TREND -->
    <div class="card chart-card">
        <div class="card-title">📈 Sales Trend</div>
        <canvas id="trendChart" height="90"></canvas>
    </div>

    <!-- BAR -->
    <div class="card chart-card">
        <div class="card-title">📊 Sales by Branch</div>
        <canvas id="branchChart" height="95"></canvas>
    </div>

    <!-- TABLE -->
    <div class="card">
        <div class="card-title">🏆 Branch Performance</div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th width="8%">#</th>
                        <th>Branch</th>
                        <th class="right">Sales</th>
                        <th class="right">%</th>
                        <th class="right">Transactions</th>
                    </tr>
                </thead>

                <tbody id="branchTable">
                    @forelse($branches as $index => $branch)
                    <tr class="{{ $index == 0 ? 'top-row' : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $branch->branch_name }}</td>
                        <td class="right">₱{{ number_format($branch->total_sales, 2) }}</td>
                        <td class="right">{{ $branch->percentage }}%</td>
                        <td class="right">{{ $branch->transactions }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="empty">No branch data found.</td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
let isDownloading = false;

document.querySelectorAll('.export-btn').forEach(link => {
    link.addEventListener('click', () => {
        isDownloading = true;
    });
});

let branchChart = new Chart(document.getElementById('branchChart'), {
    type: 'bar',
    data: {
        labels: @json($chartLabels),
        datasets: [{
            label: 'Sales',
            data: @json($chartData),
            borderWidth: 1,
            borderRadius: 8
        }]
    },
    options: {
        responsive:true,
        plugins:{ legend:{ display:false } },
        scales:{ y:{ beginAtZero:true } }
    }
});

let trendChart = new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: @json($trendLabels),
        datasets: [{
            label: 'Sales Trend',
            data: @json($trendData),
            fill:true,
            borderWidth:3,
            tension:.35,
            pointRadius:4
        }]
    },
    options:{
        responsive:true,
        plugins:{ legend:{ display:false } },
        scales:{ y:{ beginAtZero:true } }
    }
});

function updateClock(){
    document.getElementById('lastUpdated').innerText =
        "Last updated: " + new Date().toLocaleTimeString();
}

updateClock();
setInterval(updateClock,1000);

/* SILENT UPDATE DATA */
setInterval(() => {

    if (isDownloading) return;

    fetch(`/admin/sales/branch/data?range={{ $range }}&branch_id={{ request('branch_id') }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}`)
        .then(res => res.json())
        .then(data => {

            // KPI
            document.getElementById('grandTotal').innerText =
                '₱' + Number(data.grandTotal).toLocaleString();

            document.getElementById('totalTransactions').innerText =
                data.totalTransactions;

            if(data.average){
                document.getElementById('averageSale').innerText =
                    '₱' + Number(data.average).toLocaleString();
            }

            if(data.topBranch){
                document.getElementById('topBranch').innerText =
                    data.topBranch;
            }

            if(data.lowestBranch){
                document.getElementById('lowestBranch').innerText =
                    data.lowestBranch;
            }

            // TABLE
            let table = document.getElementById('branchTable');
            table.innerHTML = '';

            if(data.branches.length > 0){

                data.branches.forEach((b, i) => {
                    table.innerHTML += `
                        <tr class="${i === 0 ? 'top-row' : ''}">
                            <td>${i + 1}</td>
                            <td>${b.branch_name}</td>
                            <td class="right">₱${Number(b.total_sales).toLocaleString()}</td>
                            <td class="right">${b.percentage}%</td>
                            <td class="right">${b.transactions}</td>
                        </tr>
                    `;
                });

            }else{
                table.innerHTML = `
                    <tr>
                        <td colspan="5" class="empty">No branch data found.</td>
                    </tr>
                `;
            }

            // BAR CHART
            branchChart.data.labels = data.chartLabels;
            branchChart.data.datasets[0].data = data.chartData;
            branchChart.update();

            // TREND CHART
            if(data.trendLabels && data.trendData){
                trendChart.data.labels = data.trendLabels;
                trendChart.data.datasets[0].data = data.trendData;
                trendChart.update();
            }

        });

}, 5000);
</script>

@endsection