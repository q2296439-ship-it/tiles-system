<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            background: #f1f5f9;
            min-height: 100vh;
        }

        .sidebar {
            width: 240px;
            min-height: 100vh;
            background: #1e293b;
            color: white;
            padding: 20px;
        }

        .sidebar h2 {
            margin-bottom: 25px;
        }

        .menu-title {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 20px;
            margin-bottom: 8px;
        }

        .sidebar a {
            display: block;
            color: #cbd5e1;
            text-decoration: none;
            padding: 6px 0;
            font-size: 14px;
        }

        .sidebar a:hover {
            color: white;
        }

        .logout-btn {
            background: none;
            border: none;
            color: #cbd5e1;
            cursor: pointer;
            text-align: left;
            padding: 0;
            font-size: 14px;
            margin-top: 6px;
        }

        .logout-btn:hover {
            color: white;
        }

        .main {
            flex: 1;
        }

        .topbar {
            background: white;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .content {
            padding: 20px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .card {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .kpi { color: white; }

        .blue { background: #3b82f6; }
        .green { background: #22c55e; }
        .yellow { background: #f59e0b; }
        .red { background: #ef4444; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f9fafb;
        }

        th, td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        tr:hover {
            background: #f1f5f9;
        }

        .low {
            color: red;
            font-weight: bold;
        }

        .section-title {
            margin-bottom: 10px;
            font-weight: bold;
        }
    </style>
</head>

<body>

<div class="sidebar">
    <h2>Admin Panel</h2>

    <div class="menu-title">MAIN</div>
    <a href="/admin/dashboard">📊 Dashboard</a>

    <div class="menu-title">POS / SALES</div>
    <a href="/admin/pos">💰 POS</a>
    <a href="#">📊 Per Brand</a>
    <a href="#">🏬 Per Branch</a>
    <a href="/admin/sales/daily">📅 Daily Sales</a>

    <div class="menu-title">PRODUCT</div>
    <a href="#">📦 Product Overview</a>
    <a href="#">🏷 Per Model</a>

    <div class="menu-title">INVENTORY</div>
    <a href="/admin/inventory">📦 Overview Stock</a>
    <a href="#">➕ Add New Stock</a>
    <a href="#">⬅ Transfer In</a>
    <a href="#">➡ Transfer Out</a>
    <a href="#">📄 Delivery Report</a>

    <div class="menu-title">USER</div>
    <a href="/admin/users">➕ Add User</a>
    <a href="/admin/users">👥 Manage Account</a>

    <a href="/admin/branches">🏢 Add Branch</a>

    <div class="menu-title">ACCOUNT</div>

    <form method="POST" action="/logout">
        @csrf
        <button type="submit" class="logout-btn">🚪 Logout</button>
    </form>
</div>

<div class="main">

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

</body>
</html>