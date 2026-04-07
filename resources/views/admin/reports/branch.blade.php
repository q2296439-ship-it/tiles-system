@extends('layouts.admin')

@section('content')

<div class="content">

    <!-- 🔥 HEADER -->
    <div style="margin-bottom:25px;">

        <!-- TITLE + LAST UPDATED -->
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2 style="margin:0;">📊 Sales per Branch <span style="color:green;">● Live</span></h2>
            <small id="lastUpdated" style="color:#64748b;">
                Last updated: --
            </small>
        </div>

        <!-- RANGE -->
        <div style="margin-top:15px;">
            <form method="GET">
                <select name="range" onchange="this.form.submit()" style="padding:6px 10px;">
                    <option value="today" {{ $range=='today'?'selected':'' }}>Today</option>
                    <option value="week" {{ $range=='week'?'selected':'' }}>This Week</option>
                    <option value="month" {{ $range=='month'?'selected':'' }}>This Month</option>
                </select>
            </form>
        </div>

        <!-- DATE + FILTER + ACTIONS -->
        <div style="margin-top:15px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">

            <!-- 🔥 SINGLE FORM (AUTO SUBMIT NA) -->
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

                <!-- 🔥 AUTO CHANGE BRANCH -->
                <select name="branch_id"
                    onchange="this.form.submit()"
                    style="padding:6px;">
                    <option value="">All Branch</option>

                    @foreach($branchList as $b)
                        <option value="{{ $b->id }}"
                            {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                            {{ $b->name }}
                        </option>
                    @endforeach
                </select>

            </form>

            <!-- 🔥 EXCEL -->
            <a href="/admin/sales/branch/excel?range={{ $range }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}&branch_id={{ request('branch_id') }}">
                <button style="padding:6px 12px; background:green; color:white;">
                    📊 Export Excel
                </button>
            </a>

            <!-- 🔥 PDF -->
            <a href="/admin/sales/branch/pdf?range={{ $range }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}&branch_id={{ request('branch_id') }}" target="_blank">
                <button style="padding:6px 12px;">
                    📄 Export PDF
                </button>
            </a>

        </div>

    </div>

    <!-- ALERTS -->
    @if(isset($alerts) && count($alerts) > 0)
    <div class="card" style="margin-bottom:25px; background:#fff3cd; padding:15px;">
        <strong>⚠ Alerts</strong>
        <ul style="margin-top:10px; padding-left:20px;">
            @foreach($alerts as $alert)
                <li>{{ $alert }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- KPI -->
    <div style="display:grid; grid-template-columns:repeat(5,1fr); gap:20px; margin-bottom:30px;">

        <div class="card">
            <p>Total Sales</p>
            <h2 id="grandTotal">₱{{ number_format($grandTotal, 2) }}</h2>
        </div>

        <div class="card">
            <p>Transactions</p>
            <h2 id="totalTransactions">{{ $totalTransactions }}</h2>
        </div>

        <div class="card">
            <p>Average Sale</p>
            <h2>₱{{ number_format($average, 2) }}</h2>
        </div>

        <div class="card">
            <p>Top Branch 🥇</p>
            <h2>{{ $topBranch->branch_name ?? '-' }}</h2>
        </div>

        <div class="card">
            <p>Lowest Branch ⚠</p>
            <h2>{{ $lowestBranch->branch_name ?? '-' }}</h2>
        </div>

    </div>

    <!-- TREND -->
    <div class="card" style="padding:20px; margin-bottom:30px;">
        <strong>📈 Sales Trend</strong>
        <canvas id="trendChart"></canvas>
    </div>

    <!-- BAR -->
    <div class="card" style="padding:20px; margin-bottom:30px;">
        <strong>📊 Sales by Branch</strong>
        <canvas id="branchChart"></canvas>
    </div>

    <!-- TABLE -->
    <div class="card" style="padding:20px;">
        <strong>📊 Branch Performance</strong>

        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Branch</th>
                    <th style="text-align:right;">Sales</th>
                    <th style="text-align:right;">%</th>
                    <th style="text-align:right;">Transactions</th>
                </tr>
            </thead>

            <tbody id="branchTable">
                @foreach($branches as $index => $branch)
                <tr style="{{ $index == 0 ? 'background:#d1fae5;font-weight:bold;' : '' }}">
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $branch->branch_name }}</td>
                    <td style="text-align:right;">₱{{ number_format($branch->total_sales, 2) }}</td>
                    <td style="text-align:right;">{{ $branch->percentage }}%</td>
                    <td style="text-align:right;">{{ $branch->transactions }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>

<!-- CHART -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let branchChart = new Chart(document.getElementById('branchChart'), {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Sales',
                data: @json($chartData),
                borderWidth: 1
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });

    let trendChart = new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: @json($trendLabels),
            datasets: [{
                label: 'Sales Trend',
                data: @json($trendData),
                borderWidth: 2,
                tension: 0.3
            }]
        }
    });

    document.getElementById('lastUpdated').innerText =
        "Last updated: " + new Date().toLocaleTimeString();

    setInterval(() => {
        fetch(`/admin/sales/branch/data?range={{ $range }}&branch_id={{ request('branch_id') }}`)
            .then(res => res.json())
            .then(data => {

                document.getElementById('grandTotal').innerText =
                    '₱' + Number(data.grandTotal).toLocaleString();

                document.getElementById('totalTransactions').innerText =
                    data.totalTransactions;

                let table = document.getElementById('branchTable');
                table.innerHTML = '';

                data.branches.forEach((b, i) => {
                    table.innerHTML += `
                        <tr ${i === 0 ? 'style="background:#d1fae5;font-weight:bold;"' : ''}>
                            <td>${i + 1}</td>
                            <td>${b.branch_name}</td>
                            <td style="text-align:right;">₱${Number(b.total_sales).toLocaleString()}</td>
                            <td style="text-align:right;">${b.percentage}%</td>
                            <td style="text-align:right;">${b.transactions}</td>
                        </tr>
                    `;
                });

                branchChart.data.labels = data.chartLabels;
                branchChart.data.datasets[0].data = data.chartData;
                branchChart.update();

                document.getElementById('lastUpdated').innerText =
                    "Last updated: " + new Date().toLocaleTimeString();

            });
    }, 5000);
</script>

@endsection