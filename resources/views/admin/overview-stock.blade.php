@extends('layouts.admin')

@section('content')

<style>
.container {
    max-width: 1250px;
    margin: auto;
    font-family: 'Segoe UI', Tahoma, sans-serif;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
}

.header h2 {
    font-size: 22px;
    font-weight: 600;
}

.filters {
    display: flex;
    gap: 10px;
}

.search, .select {
    padding: 10px 12px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    outline: none;
}

.search:focus, .select:focus {
    border-color: #3b82f6;
}

.stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 15px;
    margin-bottom: 25px;
}

.stat {
    background: white;
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.05);
}

.stat small {
    color: #6b7280;
}

.stat h2 {
    margin-top: 8px;
    font-size: 22px;
}

.card {
    background: white;
    border-radius: 14px;
    padding: 20px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 15px;
}

th {
    background: #f9fafb;
    text-align: left;
    font-weight: 600;
    font-size: 13px;
    color: #374151;
}

th, td {
    padding: 14px;
    border-bottom: 1px solid #e5e7eb;
}

tr:hover {
    background: #f9fafb;
}

.low-row {
    background: #fff1f2;
}

.badge {
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
}

.low { background: #fee2e2; color: #dc2626; }
.ok { background: #dcfce7; color: #16a34a; }

.pagination {
    margin-top: 15px;
}
</style>

<div class="container">

    <div class="header">
        <h2>📦 Overview Stock</h2>

        <div class="filters">
            <input type="text" id="searchInput" class="search" placeholder="🔍 Search product...">

            <select class="select">
                <option>All Branch</option>
                @foreach($branches as $b)
                    <option>{{ $b->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- STATS --}}
    <div class="stats">
        <div class="stat">
            <small>Total Products</small>
            <h2>{{ $products->count() }}</h2>
        </div>

        <div class="stat">
            <small>Showing</small>
            <h2>{{ $products->count() }}</h2>
        </div>

        <div class="stat">
            <small>Low Stock</small>
            <h2>{{ $products->filter(fn($p) => $p->stock <= 10)->count() }}</h2>
        </div>

        <div class="stat">
            <small>Total Value</small>
            <h2>₱{{ number_format($products->sum(fn($p) => $p->price * $p->stock), 2) }}</h2>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="card">

        <table id="productTable">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Branch</th>
                    <th>Size</th>
                    <th>Color</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
            @forelse($products as $p)
            <tr class="{{ $p->stock <= 10 ? 'low-row' : '' }}">
                <td><strong>{{ $p->name }}</strong></td>
                <td>{{ $p->branch->name ?? '-' }}</td>
                <td>{{ $p->size }}</td>
                <td>{{ $p->color }}</td>
                <td>₱{{ number_format($p->price, 2) }}</td>
                <td>{{ $p->stock }}</td>
                <td>
                    @if($p->stock <= 10)
                        <span class="badge low">Low</span>
                    @else
                        <span class="badge ok">OK</span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align:center;">No products found</td>
            </tr>
            @endforelse
            </tbody>
        </table>

        {{-- PAGINATION --}}
        <div class="pagination">
            {{ $products->links() }}
        </div>

    </div>

</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let value = this.value.toLowerCase();
    let rows = document.querySelectorAll("#productTable tbody tr");

    rows.forEach((row) => {
        row.style.display = row.innerText.toLowerCase().includes(value) ? "" : "none";
    });
});
</script>

@endsection