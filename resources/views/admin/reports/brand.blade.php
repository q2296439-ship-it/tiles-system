@extends('layouts.admin')

@section('content')

<div class="content">

    <!-- 🔥 HEADER -->
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
        <h2>📊 Sales per Brand <span style="color:green;">● Live</span></h2>

        <small style="color:#64748b;">
            Last updated: {{ now()->format('h:i:s A') }}
        </small>
    </div>

    <!-- 🔥 FILTER + EXPORT -->
    <div style="margin-bottom:20px;">
        <form method="GET" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">

            <input type="date" name="start_date" value="{{ request('start_date') }}">
            <span>To</span>
            <input type="date" name="end_date" value="{{ request('end_date') }}">

            <!-- 🔥 NEW: BRANCH FILTER -->
            <select name="branch_id">
                <option value="">All Branch</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}" 
                        {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach
            </select>

            <button class="btn">Filter</button>

            <!-- 🔥 EXPORT BUTTONS -->
            <a href="{{ route('report.brand.excel', request()->all()) }}" class="btn excel">
                📊 Export Excel
            </a>

            <a href="{{ route('report.brand.pdf', request()->all()) }}" class="btn pdf">
                📄 Export PDF
            </a>

        </form>
    </div>

    <!-- 🔥 KPI CARDS -->
    <div style="display:grid; grid-template-columns:repeat(4,1fr); gap:20px; margin-bottom:20px;">

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
    <div class="card" style="margin-bottom:20px;">
        <div style="font-weight:bold; margin-bottom:10px;">
            📈 Sales by Brand
        </div>

        <canvas id="brandChart" height="100"></canvas>
    </div>

    <!-- 🔥 TABLE -->
    <div class="card">

        <div style="font-weight:bold; margin-bottom:10px;">
            🧾 Brand Breakdown
        </div>

        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Brand</th>
                    <th>Total Sales</th>
                    <th>% Share</th>
                </tr>
            </thead>

            <tbody>
                @php $total = $totals->sum(); @endphp

                @forelse($data as $index => $row)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $row->brand }}</td>
                    <td>₱{{ number_format($row->total, 2) }}</td>
                    <td>
                        {{ $total > 0 ? number_format(($row->total / $total) * 100, 2) : 0 }}%
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align:center;">No data available</td>
                </tr>
                @endforelse
            </tbody>

        </table>

    </div>

</div>

<!-- 🔥 STYLES -->
<style>
.btn {
    padding: 6px 12px;
    border-radius: 4px;
    border: none;
    background: #3b82f6;
    color: white;
    cursor: pointer;
    font-size: 13px;
}

.btn.excel {
    background: #16a34a;
}

.btn.pdf {
    background: #e5e7eb;
    color: black;
}

.card {
    background: white;
    padding: 15px;
    border-radius: 6px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

table {
    width: 100%;
    border-collapse: collapse;
}

table th, table td {
    padding: 10px;
    border-bottom: 1px solid #eee;
}
</style>

<!-- 🔥 CHART JS -->
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
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

@endsection