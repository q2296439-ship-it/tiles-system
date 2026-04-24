@php
$layout = match(strtolower(auth()->user()->role)) {
    'admin'   => 'layouts.admin',
    'manager' => 'layouts.manager',
    'audit'   => 'layouts.manager',
    default   => 'layouts.cashier',
};
@endphp

@extends($layout)

@section('content')

<style>
.container{
    max-width:1300px;
    margin:auto;
    font-family:'Segoe UI',Tahoma,sans-serif;
}

.header{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    margin-bottom:25px;
    flex-wrap:wrap;
}

.header h2{
    font-size:24px;
    font-weight:700;
    margin:0;
    color:#111827;
}

.filters{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
}

.search,.select{
    padding:11px 14px;
    border:1px solid #d1d5db;
    border-radius:10px;
    font-size:14px;
    min-width:210px;
    outline:none;
    background:#fff;
}

.search:focus,.select:focus{
    border-color:#3b82f6;
    box-shadow:0 0 0 3px rgba(59,130,246,.10);
}

.btn{
    background:#3b82f6;
    color:#fff;
    border:none;
    padding:11px 16px;
    border-radius:10px;
    cursor:pointer;
    font-size:14px;
    font-weight:600;
    text-decoration:none;
    display:inline-block;
    transition:.2s;
}

.btn:hover{
    background:#2563eb;
}

.btn-success{
    background:#16a34a;
}

.btn-success:hover{
    background:#15803d;
}

.btn-danger{
    background:#dc2626;
}

.btn-danger:hover{
    background:#b91c1c;
}

.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:15px;
    margin-bottom:25px;
}

.stat{
    background:#fff;
    padding:22px;
    border-radius:14px;
    box-shadow:0 6px 18px rgba(0,0,0,0.05);
}

.stat small{
    color:#6b7280;
    font-size:13px;
}

.stat h2{
    margin:8px 0 0;
    font-size:24px;
    font-weight:700;
    color:#111827;
}

.card{
    background:#fff;
    border-radius:14px;
    padding:22px;
    box-shadow:0 6px 18px rgba(0,0,0,0.06);
    overflow-x:auto;
}

.table-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:15px;
    gap:10px;
    flex-wrap:wrap;
}

table{
    width:100%;
    border-collapse:collapse;
}

th{
    background:#f9fafb;
    text-align:left;
    font-weight:700;
    font-size:13px;
    color:#374151;
    white-space:nowrap;
}

th,td{
    padding:14px;
    border-bottom:1px solid #e5e7eb;
    font-size:14px;
}

tbody tr:hover{
    background:#f9fafb;
}

.low-row{
    background:#fff7ed;
}

.badge{
    padding:5px 10px;
    border-radius:999px;
    font-size:11px;
    font-weight:700;
    display:inline-block;
}

.low{
    background:#fee2e2;
    color:#dc2626;
}

.ok{
    background:#dcfce7;
    color:#16a34a;
}

.pagination{
    margin-top:18px;
    display:flex;
    justify-content:center;
    align-items:center;
    gap:6px;
    flex-wrap:wrap;
}

.pagination svg{
    width:18px !important;
    height:18px !important;
}

svg{
    max-width:18px;
    max-height:18px;
}

.empty{
    text-align:center;
    padding:20px;
    color:#6b7280;
}
</style>

<div class="container">

    <div class="header">
        <h2>📦 Overview Stock</h2>

        <form method="GET" class="filters">

            <input
                type="text"
                name="search"
                value="{{ request('search') }}"
                placeholder="🔍 Search product..."
                class="search">

            <select name="branch_id" class="select">
                <option value="">All Branch</option>
                @foreach($branches as $b)
                    <option value="{{ $b->id }}"
                        {{ request('branch_id') == $b->id ? 'selected' : '' }}>
                        {{ $b->name }}
                    </option>
                @endforeach
            </select>

            <button type="submit" class="btn">Filter</button>

            @php
                $baseUrl = request()->is('cashier/*')
                    ? '/admin/inventory/export'
                    : '/admin/inventory/export';
            @endphp

            <a href="{{ url($baseUrl . '/excel?search=' . request('search') . '&branch_id=' . request('branch_id')) }}"
               class="btn btn-success">
               📊 Excel
            </a>

            <a href="{{ url($baseUrl . '/pdf?search=' . request('search') . '&branch_id=' . request('branch_id')) }}"
               target="_blank"
               class="btn btn-danger">
               🧾 PDF
            </a>

        </form>
    </div>

    <div class="stats">

        <div class="stat">
            <small>Total Products</small>
            <h2>{{ $totalProducts }}</h2>
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

    <div class="card">

        <div class="table-top">
            <strong>Inventory List</strong>
            <small>{{ $products->count() }} row(s) on this page</small>
        </div>

        <table>
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
                            <span class="badge low">Low Stock</span>
                        @else
                            <span class="badge ok">Available</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="empty">No products found</td>
                </tr>
            @endforelse
            </tbody>
        </table>

        <div class="pagination">
            {{ $products->links() }}
        </div>

    </div>

</div>

@endsection