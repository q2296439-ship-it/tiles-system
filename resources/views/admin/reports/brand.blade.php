@extends('layouts.admin')

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
    color:#111827;
    margin:0;
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

.filter-card,
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
    min-width:170px;
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

.kpi-grid{
    display:grid;
    grid-template-columns:repeat(4,1fr);
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
    margin-bottom:10px;
    font-size:14px;
}

.kpi h2{
    margin:0;
    font-size:32px;
    font-weight:800;
}

.blue{background:linear-gradient(135deg,#3b82f6,#2563eb);}
.green{background:linear-gradient(135deg,#22c55e,#16a34a);}
.orange{background:linear-gradient(135deg,#f59e0b,#d97706);}
.red{background:linear-gradient(135deg,#ef4444,#dc2626);}

.card-title{
    font-size:22px;
    font-weight:800;
    margin-bottom:16px;
    color:#111827;
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

@media (max-width:1100px){
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
            <h1 class="page-title">📊 Sales per Brand <span class="live">● Live</span></h1>
            <div class="page-sub">Track brand performance, totals, and rankings across branches.</div>
        </div>

        <div class="last-updated" id="lastUpdated">
            Last updated: --
        </div>

    </div>

    <!-- FILTERS -->
    <div class="filter-card">
        <form method="GET" class="filters">

            <input type="date" name="start_date"
                value="{{ request('start_date') }}">

            <input type="date" name="end_date"
                value="{{ request('end_date') }}">

            <select name="branch_id">
                <option value="">All Branches</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}"
                        {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                        {{ $b->name }}
                    </option>
                @endforeach
            </select>

            <button class="btn btn-green" type="submit">Filter</button>

            <a href="{{ route('report.brand.excel', request()->all()) }}"
               class="btn btn-green export-btn">
               📊 Excel
            </a>

            <a href="{{ route('report.brand.pdf', request()->all()) }}"
               target="_blank"
               class="btn btn-gray export-btn">
               📄 PDF
            </a>

        </form>
    </div>

    <!-- KPI -->
    <div class="kpi-grid">

        <div class="kpi blue">
            <small>Total Sales</small>
            <h2>₱{{ number_format($totals->sum(), 2) }}</h2>
        </div>

        <div class="kpi green">
            <small>Total Brands</small>
            <h2>{{ count($data) }}</h2>
        </div>

        <div class="kpi orange">
            <small>Top Brand</small>
            <h2>{{ $data->first()->brand ?? '-' }}</h2>
        </div>

        <div class="kpi red">
            <small>Average</small>
            <h2>₱{{ count($data) ? number_format($totals->sum() / count($data), 2) : 0 }}</h2>
        </div>

    </div>

    <!-- CHART -->
    <div class="card" style="margin-bottom:22px;">
        <div class="card-title">📈 Sales by Brand</div>
        <canvas id="brandChart" height="95"></canvas>
    </div>

    <!-- TABLE -->
    <div class="card">
        <div class="card-title">🏆 Brand Performance</div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th width="8%">#</th>
                        <th>Brand</th>
                        <th class="right">Sales</th>
                        <th class="right">%</th>
                    </tr>
                </thead>

                <tbody>
                    @php $total = $totals->sum(); @endphp

                    @forelse($data as $index => $row)
                    <tr class="{{ $index == 0 ? 'top-row' : '' }}">
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $row->brand }}</td>
                        <td class="right">₱{{ number_format($row->total, 2) }}</td>
                        <td class="right">
                            {{ $total > 0 ? number_format(($row->total / $total) * 100, 2) : 0 }}%
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="empty">No sales data found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:20px; display:flex; justify-content:center;">
            {{ $data->links('pagination::bootstrap-4') }}
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('brandChart'), {
    type: 'bar',
    data: {
        labels: @json($labels),
        datasets: [{
            label: 'Sales',
            data: @json($totals),
            borderWidth: 1,
            borderRadius: 8
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: { beginAtZero: true }
        }
    }
});

document.getElementById('lastUpdated').innerText =
    "Last updated: " + new Date().toLocaleTimeString();

let isDownloading = false;

document.querySelectorAll('.export-btn').forEach(link => {
    link.addEventListener('click', () => {
        isDownloading = true;
    });
});

setInterval(() => {
    if (!isDownloading) {
        fetch(window.location.href, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            const newTable = doc.querySelector('tbody');
            if (newTable) {
                document.querySelector('tbody').innerHTML = newTable.innerHTML;
            }

            document.getElementById('lastUpdated').innerText =
                "Last updated: " + new Date().toLocaleTimeString();
        });
    }
}, 5000);
</script>

@endsection