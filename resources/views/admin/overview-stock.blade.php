@extends('layouts.admin')

@section('content')

<style>
.container {
    max-width: 1200px;
    margin: auto;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

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

.card {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
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

.low-row {
    background: #fff1f2;
}

.badge {
    padding: 4px 8px;
    border-radius: 5px;
    font-size: 11px;
}

.low { background: #fee2e2; color: #dc2626; }
.ok { background: #dcfce7; color: #16a34a; }

</style>

<div class="container">

    <div class="header">
        <h2>📦 Overview Stock</h2>
    </div>

    {{-- STATS --}}
    <div class="stats">
        <div class="stat">
            <small>Total Products</small>
            <h2>{{ $products->total() }}</h2>
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

        <input type="text" id="searchInput" class="search" placeholder="🔍 Search product...">

        <table id="productTable">
            <tr>
                <th>Product</th>
                <th>Branch</th>
                <th>Size</th>
                <th>Color</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Status</th>
            </tr>

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
        </table>

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

@endsection