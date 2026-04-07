<!DOCTYPE html>
<html>
<head>
    <title>Products ERP</title>

    <style>
        body {
            font-family: Arial;
            background: #f1f5f9;
            margin: 0;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        h1 {
            margin: 0;
        }

        .top-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            color: white;
            transition: 0.2s;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            opacity: 0.85;
        }

        .green { background: #22c55e; }
        .gray { background: #64748b; }
        .blue { background: #3b82f6; }

        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat {
            background: white;
            padding: 18px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .stat h4 {
            margin: 0;
            font-size: 12px;
            color: #64748b;
        }

        .stat h2 {
            margin: 5px 0 0;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }

        .top-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            align-items: center;
        }

        .search {
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            width: 250px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f8fafc;
            text-align: left;
            font-weight: 600;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
        }

        th.right, td.right { text-align: right; }
        th.center, td.center { text-align: center; }

        tr:hover {
            background: #f1f5f9;
        }

        tr.low-row {
            background: #fff1f2;
        }

        .actions {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .actions a {
            padding: 6px 10px;
            border-radius: 5px;
            color: white;
            font-size: 12px;
            text-decoration: none;
        }

        .edit { background: #3b82f6; }
        .delete { background: #ef4444; }

        .badge {
            padding: 4px 8px;
            border-radius: 5px;
            font-size: 11px;
            font-weight: 500;
        }

        .low { background: #fee2e2; color: #dc2626; }
        .ok { background: #dcfce7; color: #16a34a; }

        .empty {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
        }

        .pagination {
            margin-top: 20px;
        }

        @media print {
            .top-actions, .search {
                display: none;
            }
        }
    </style>
</head>

<body>

<div class="header">
    <h1>📦 Products</h1>

    <div class="top-actions">
        <a href="/admin" class="btn gray">← Dashboard</a>
        <a href="/admin/products/create" class="btn green">+ Add Product</a>
        <a href="/admin/products/export" class="btn blue">Export CSV</a>
        <button onclick="window.print()" class="btn gray">Print</button>
    </div>
</div>

<div class="stats">
    <div class="stat">
        <h4>Total Products</h4>
        <h2>{{ $products->total() }}</h2>
    </div>

    <div class="stat">
        <h4>Showing</h4>
        <h2>{{ $products->count() }}</h2>
    </div>

    <div class="stat">
        <h4>Low Stock</h4>
        <h2>{{ $products->filter(fn($p) => $p->stock <= 10)->count() }}</h2>
    </div>

    <div class="stat">
        <h4>Total Value</h4>
        <h2>₱{{ number_format($products->sum(fn($p) => $p->price * $p->stock), 2) }}</h2>
    </div>
</div>

<div class="card">

    <div class="top-bar">
        <input type="text" id="searchInput" class="search" placeholder="🔍 Search product...">
    </div>

    <table id="productTable">
        <tr>
            <th>Name</th>
            <th>Branch</th> <!-- 🔥 ADDED -->
            <th>Size</th>
            <th>Color</th>
            <th class="right">Price</th>
            <th class="center">Stock</th>
            <th class="center">Status</th>
            <th class="right">Action</th>
        </tr>

        @forelse($products as $p)
        <tr class="{{ $p->stock <= 10 ? 'low-row' : '' }}">
            <td><strong>{{ $p->name }}</strong></td>

            <!-- 🔥 FIX: SHOW BRANCH -->
            <td>{{ $p->branch->name ?? '-' }}</td>

            <td>{{ $p->size }}</td>
            <td>{{ $p->color }}</td>
            <td class="right">₱{{ number_format($p->price, 2) }}</td>
            <td class="center">{{ $p->stock }}</td>

            <td class="center">
                @if($p->stock <= 10)
                    <span class="badge low">⚠ Low</span>
                @else
                    <span class="badge ok">✔ OK</span>
                @endif
            </td>

            <td>
                <div class="actions">
                    <!-- 🔥 FIXED ROUTES -->
                    <a href="/admin/products/{{ $p->id }}/edit" class="edit">Edit</a>
                    <a href="/admin/products/{{ $p->id }}/delete" class="delete">Delete</a>
                </div>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="empty">No products found</td>
        </tr>
        @endforelse
    </table>

    <div class="pagination">
        {{ $products->links() }}
    </div>

</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#productTable tr");

    rows.forEach((row, index) => {
        if(index === 0) return;
        row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
    });
});
</script>

</body>
</html>