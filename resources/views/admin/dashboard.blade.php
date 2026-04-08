@extends('layouts.admin')

@section('content')

<div class="content">

    <div class="topbar">
        <div><strong>Dashboard</strong></div>

        <div>
            <form method="GET">
                <select name="branch_id" onchange="this.form.submit()">
                    <option value="">All Branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}"
                            {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </form>

            <span style="margin-left:10px;">👤 Admin</span>
        </div>
    </div>

    <div class="content">

        <div class="grid">
            <div class="card kpi blue">
                <h4>Total Products</h4>
                <h2>{{ $totalProducts }}</h2>
            </div>

            <div class="card kpi green">
                <h4>Today Sales</h4>
                <h2>₱{{ number_format($todaySales, 2) }}</h2>
            </div>

            <div class="card kpi yellow">
                <h4>Transactions</h4>
                <h2>{{ $transactionsToday }}</h2>
            </div>

            <div class="card kpi red">
                <h4>Low Stocks</h4>
                <h2>{{ $lowStocks->count() }}</h2>
            </div>
        </div>

        <div class="card">
            <div class="section-title">📊 Sales Overview</div>
            <canvas id="salesChart"></canvas>
        </div>

        <div class="card">
            <div class="section-title">🧾 Recent Transactions</div>

            <table>
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                </tr>

                @foreach($recentSales as $sale)
                <tr>
                    <td>{{ $sale->created_at }}</td>
                    <td>₱{{ number_format($sale->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </table>
        </div>

        <div class="card">
            <div class="section-title">⚠ Low Stock</div>

            <table>
                <tr>
                    <th>Product</th>
                    <th>Branch</th>
                    <th>Stock</th>
                </tr>

                @forelse($lowStocks as $item)
                    <tr>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->branch_name }}</td>
                        <td class="low">{{ $item->stock }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">No low stock items</td>
                    </tr>
                @endforelse
            </table>
        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    const labels = {!! json_encode($salesData->keys()) !!};
    const data = {!! json_encode($salesData->values()) !!};

    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Sales',
                data: data,
                borderWidth: 2,
                tension: 0.3
            }]
        }
    });
</script>

@endsection