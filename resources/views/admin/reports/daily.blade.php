@extends('layouts.admin')

@section('content')

<div class="content">

    <!-- 🔥 HEADER -->
    <div style="margin-bottom:25px;">

        <!-- TITLE -->
        <div style="display:flex; justify-content:space-between; align-items:center;">
            <h2 style="margin:0;">📊 Sales Dashboard <span style="color:green;">● Live</span></h2>

            <small id="lastUpdate" style="color:#64748b;">
                Last updated: --
            </small>
        </div>

        <!-- RANGE -->
        <div style="margin-top:15px;">
            <form method="GET">
                <select name="range" onchange="this.form.submit()"
                    style="padding:6px 10px;">
                    <option value="daily" {{ $range=='daily'?'selected':'' }}>Today</option>
                    <option value="week" {{ $range=='week'?'selected':'' }}>This Week</option>
                    <option value="month" {{ $range=='month'?'selected':'' }}>This Month</option>
                </select>
            </form>
        </div>

        <!-- 🔥 FILTER + EXPORT -->
        <div style="margin-top:15px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">

            <form method="GET" style="display:flex; gap:10px; align-items:center;">

                <input type="hidden" name="range" value="{{ $range }}">

                <input type="date" name="start_date"
                    value="{{ request('start_date') }}"
                    onchange="this.form.submit()"
                    style="padding:6px;">

                <span>To:</span>

                <input type="date" name="end_date"
                    value="{{ request('end_date') }}"
                    onchange="this.form.submit()"
                    style="padding:6px;">

                <!-- 🔥 NEW: BRANCH FILTER -->
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
            <a href="/admin/sales/daily/excel?range={{ $range }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}&branch_id={{ request('branch_id') }}">
                <button style="padding:6px 12px; background:green; color:white;">
                    📊 Export Excel
                </button>
            </a>

            <!-- 🔥 PDF -->
            <a href="/admin/sales/daily/pdf?range={{ $range }}&start_date={{ request('start_date') }}&end_date={{ request('end_date') }}&branch_id={{ request('branch_id') }}" target="_blank">
                <button style="padding:6px 12px;">
                    📄 Export PDF
                </button>
            </a>

        </div>

    </div>

    <!-- ALERTS -->
    @if(isset($alerts) && count($alerts) > 0)
    <div class="card" style="background:#fff3cd; margin-bottom:25px; padding:15px; border-radius:8px;">
        <strong>⚠ Alerts</strong>
        <ul style="margin-top:10px; padding-left:18px;">
            @foreach($alerts as $alert)
                <li>{{ $alert }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- KPI -->
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-bottom:30px;">

        <div class="card">
            <p style="margin:0; color:#64748b; font-size:13px;">Total Sales</p>
            <h2 style="margin-top:10px;">₱{{ number_format($total, 2) }}</h2>
        </div>

        <div class="card">
            <p style="margin:0; color:#64748b; font-size:13px;">Transactions</p>
            <h2 style="margin-top:10px;">{{ $transactionCount }}</h2>
        </div>

        <div class="card">
            <p style="margin:0; color:#64748b; font-size:13px;">Average Sale</p>
            <h2 style="margin-top:10px;">₱{{ number_format($average, 2) }}</h2>
        </div>

    </div>

    <!-- CHART -->
    <div class="card" style="padding:20px; margin-bottom:30px;">
        <div style="font-weight:bold; margin-bottom:15px;">
            📈 Sales Trend
        </div>
        <canvas id="trendChart" height="90"></canvas>
    </div>

    <!-- TABLE -->
    <div class="card" style="padding:20px;">

        <div style="font-weight:bold; margin-bottom:15px;">
            🧾 Transactions (Real-time)
        </div>

        <table style="width:100%; border-collapse:collapse; font-size:14px;">

            <thead>
                <tr style="background:#f9fafb; border-bottom:1px solid #e5e7eb;">
                    <th style="text-align:left; padding:12px;">Date & Time</th>
                    <th style="text-align:left; padding:12px;">Branch</th>
                    <th style="text-align:left; padding:12px;">Cashier</th>
                    <th style="text-align:right; padding:12px;">Amount</th>
                </tr>
            </thead>

            <tbody>
                @forelse($sales as $sale)
                <tr style="border-bottom:1px solid #e5e7eb;">
                    <td style="padding:12px;">
                        {{ \Carbon\Carbon::parse($sale->created_at)->format('M d, Y h:i:s A') }}
                    </td>

                    <td style="padding:12px;">
                        {{ $sale->branch->name ?? 'N/A' }}
                    </td>

                    <td style="padding:12px;">
                        {{ $sale->user->name ?? 'N/A' }}
                    </td>

                    <td style="padding:12px; text-align:right; font-weight:600;">
                        ₱{{ number_format($sale->total_amount, 2) }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="padding:12px; text-align:center;">
                        No sales found
                    </td>
                </tr>
                @endforelse
            </tbody>

        </table>

    </div>

</div>

<!-- CHART -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels: @json($labels),
        datasets: [{
            label: 'Sales',
            data: @json($data),
            borderWidth: 2,
            tension: 0.3
        }]
    },
    options: {
        plugins: { legend: { display: false } },
        scales: { y: { beginAtZero: true } }
    }
});

// AUTO REFRESH
setInterval(() => {
    location.reload();
}, 5000);

// CLOCK
function updateTime() {
    const now = new Date();
    document.getElementById('lastUpdate').innerText = now.toLocaleTimeString();
}
updateTime();
setInterval(updateTime, 1000);
</script>

@endsection