<!DOCTYPE html>
<html>
<head>
    <title>Inventory</title>

    <style>
        body {
            font-family: Arial, sans-serif;
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

        h1 { margin: 0; }

        .btn {
            background: #64748b;
            color: white;
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            border: none;
            cursor: pointer;
        }

        .btn:hover { background: #475569; }

        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }

        .summary {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .box {
            background: white;
            padding: 15px;
            border-radius: 10px;
            flex: 1;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 14px;
            border-bottom: 1px solid #e2e8f0;
        }

        th { background: #f8fafc; }

        .center { text-align: center; }
        .right { text-align: right; }

        .badge {
            padding: 5px 8px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: bold;
        }

        .ok { background: #dcfce7; color: #16a34a; }
        .low { background: #fef9c3; color: #ca8a04; }
        .out { background: #fee2e2; color: #dc2626; }

        .in { color: #16a34a; font-weight: bold; }
        .adjust { color: #f59e0b; font-weight: bold; }

        .empty {
            text-align: center;
            padding: 40px;
            color: #94a3b8;
        }

        .filter-bar {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }

        select, input {
            padding: 8px;
            border-radius: 6px;
            border: 1px solid #cbd5e1;
        }
    </style>
</head>

<body>

<div class="header">
    <h1>📊 Inventory</h1>

    <div style="display:flex; gap:10px;">
        <a href="/admin" class="btn">← Dashboard</a>
        <a href="/admin/inventory/export" class="btn">⬇ Export CSV</a>
        <a href="/admin/movements/export" class="btn">⬇ Export Movement</a>
        <button onclick="window.print()" class="btn">🖨 Print</button>
    </div>
</div>

<!-- SUMMARY -->
<div class="summary">
    <div class="box">
        <h4>Total Items</h4>
        <h2>{{ $products->count() }}</h2>
    </div>

    <div class="box">
        <h4>Total Stock</h4>
        <h2>{{ $products->sum('stock') }}</h2>
    </div>

    <div class="box">
        <h4>Total Value</h4>
        <h2>₱{{ number_format($products->sum(fn($p) => $p->stock * $p->price), 2) }}</h2>
    </div>
</div>

<!-- 🔥 STOCK TRANSFER -->
<div class="card">
    <h2>🔄 Stock Transfer</h2>

    <form method="POST" action="/admin/transfer" class="filter-bar">
        @csrf

        <select name="product_id">
            @foreach($products as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>

        <select name="from_branch">
            @foreach($branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
        </select>

        <select name="to_branch">
            @foreach($branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
        </select>

        <input type="number" name="qty" placeholder="Qty" required>

        <button class="btn">Transfer</button>
    </form>
</div>

<!-- PRODUCTS -->
<div class="card">
<h2>📦 Product Inventory</h2>

<table>
<tr>
    <th>Product</th>
    <th>Branch</th>
    <th class="center">Stock</th>
    <th class="right">Value</th>
    <th class="center">Status</th>
    <th>Last Update</th>
</tr>

@forelse($products as $p)
@php
$value = $p->stock * $p->price;
$status = $p->stock == 0 ? 'out' : ($p->stock <= 10 ? 'low' : 'ok');
@endphp

<tr>
    <td><strong>{{ $p->name }}</strong></td>
    <td>{{ $p->branch->name ?? '-' }}</td>
    <td class="center">{{ $p->stock }}</td>
    <td class="right">₱{{ number_format($value, 2) }}</td>

    <td class="center">
        @if($status == 'ok')
            <span class="badge ok">✔ OK</span>
        @elseif($status == 'low')
            <span class="badge low">⚠ LOW</span>
        @else
            <span class="badge out">❌ OUT</span>
        @endif
    </td>

    <td>{{ \Carbon\Carbon::parse($p->updated_at)->format('M d, Y h:i A') }}</td>
</tr>
@empty
<tr><td colspan="6" class="empty">No inventory data</td></tr>
@endforelse
</table>
</div>

<!-- 🔥 MOVEMENTS -->
<div class="card">

<h2>📜 Stock Movements</h2>

<form method="GET" class="filter-bar">

    <select name="type">
        <option value="">All Type</option>
        <option value="IN">IN</option>
        <option value="OUT">OUT</option>
        <option value="ADJUST">ADJUST</option>
    </select>

    <select name="branch_id">
        <option value="">All Branch</option>
        @foreach($branches as $b)
            <option value="{{ $b->id }}">{{ $b->name }}</option>
        @endforeach
    </select>

    <button class="btn">Filter</button>
</form>

<table>
<tr>
    <th>Product</th>
    <th>Branch</th>
    <th class="center">Type</th>
    <th class="center">Qty</th>
    <th>Reason</th>
    <th>Date</th>
</tr>

@forelse($movements as $m)
<tr>
    <td>{{ $m->product->name }}</td>
    <td>{{ $m->branch->name ?? '-' }}</td>

    <td class="center">
        @if($m->type == 'IN')
            <span class="in">+ IN</span>
        @elseif($m->type == 'OUT')
            <span class="out">- OUT</span>
        @else
            <span class="adjust">± ADJUST</span>
        @endif
    </td>

    <td class="center">{{ $m->quantity }}</td>
    <td>{{ $m->reason }}</td>
    <td>{{ $m->created_at }}</td>
</tr>
@empty
<tr><td colspan="6" class="empty">No movement data</td></tr>
@endforelse

</table>

</div>

</body>
</html>