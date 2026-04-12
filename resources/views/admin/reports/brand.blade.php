@extends('layouts.admin')

@section('content')

<div class="content">

    <!-- 🔥 HEADER -->
    <div style="margin-bottom:25px;">

        <!-- TITLE + LAST UPDATED -->
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2 style="margin:0;">📊 Sales per Brand <span style="color:green;">● Live</span></h2>
            <small id="lastUpdated" style="color:#64748b;">
                Last updated: --
            </small>
        </div>

        <!-- 🔥 DATE + FILTER + ACTIONS -->
        <div style="margin-top:15px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">

            <!-- FILTER FORM -->
            <form method="GET" style="display:flex; gap:10px; align-items:center;">

                <input type="date" name="start_date"
                    value="{{ request('start_date') }}"
                    onchange="this.form.submit()"
                    style="padding:6px;">

                <span>To:</span>

                <input type="date" name="end_date"
                    value="{{ request('end_date') }}"
                    onchange="this.form.submit()"
                    style="padding:6px;">

                <select name="branch_id"
                    onchange="this.form.submit()"
                    style="padding:6px;">
                    <option value="">All Branch</option>

                    @foreach($branches as $b)
                        <option value="{{ $b->id }}"
                            {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>

            </form>

            <!-- 🔥 EXPORT BUTTONS (FIXED FINAL) -->
            <a href="{{ route('report.brand.excel', request()->all()) }}"
               class="export-btn"
               style="padding:6px 12px; background:green; color:white; text-decoration:none; display:inline-block;">
                📊 Export Excel
            </a>

            <a href="{{ route('report.brand.pdf', request()->all()) }}"
               target="_blank"
               class="export-btn"
               style="padding:6px 12px; background:#e5e7eb; color:black; text-decoration:none; display:inline-block;">
                📄 Export PDF
            </a>

        </div>

    </div>

    <!-- 🔥 KPI -->
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:30px;">

        <div class="card">
            <p>Total Sales</p>
            <h2>₱{{ number_format($totals->sum(), 2) }}</h2>
        </div>

        <div class="card">
            <p>Total Brands</p>
            <h2>{{ count($data) }}</h2>
        </div>

        <div class="card">
            <p>Top Brand 🏆</p>
            <h2>{{ $data->first()->brand ?? '-' }}</h2>
        </div>

        <div class="card">
            <p>Average</p>
            <h2>
                ₱{{ count($data) ? number_format($totals->sum() / count($data), 2) : 0 }}
            </h2>
        </div>

    </div>

    <!-- 🔥 CHART -->
    <div class="card" style="padding:20px; margin-bottom:30px;">
        <strong>📊 Sales by Brand</strong>
        <canvas id="brandChart"></canvas>
    </div>

    <!-- 🔥 TABLE -->
    <div class="card" style="padding:20px;">
        <strong>📊 Brand Performance</strong>

        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Brand</th>
                    <th style="text-align:right;">Sales</th>
                    <th style="text-align:right;">%</th>
                </tr>
            </thead>

            <tbody>
                @php $total = $totals->sum(); @endphp

                @foreach($data as $index => $row)
                <tr style="{{ $index == 0 ? 'background:#d1fae5;font-weight:bold;' : '' }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->brand }}</td>
                    <td style="text-align:right;">₱{{ number_format($row->total, 2) }}</td>
                    <td style="text-align:right;">
                        {{ $total > 0 ? number_format(($row->total / $total) * 100, 2) : 0 }}%
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

<!-- 🔥 CHART -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('brandChart'), {
    type: 'bar',
    data: {
        labels: @json($labels),
        datasets: [{
            label: 'Sales',
            data: @json($totals),
            borderWidth: 1
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// 🔥 LIVE CLOCK
document.getElementById('lastUpdated').innerText =
    "Last updated: " + new Date().toLocaleTimeString();

// 🔥 SAFE AUTO REFRESH
let isDownloading = false;

document.querySelectorAll('.export-btn').forEach(link => {
    link.addEventListener('click', () => {
        isDownloading = true;
    });
});

setInterval(() => {
    if (!isDownloading) {
        location.reload();
    }
}, 5000);
</script>

@endsection