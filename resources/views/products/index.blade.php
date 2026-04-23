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
    margin:0;
    font-size:24px;
    font-weight:700;
    color:#111827;
}

.top-actions{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}

.btn{
    padding:11px 16px;
    border-radius:10px;
    text-decoration:none;
    font-size:14px;
    color:#fff;
    border:none;
    cursor:pointer;
    font-weight:600;
    transition:.2s;
    display:inline-block;
}

.btn:hover{
    opacity:.92;
}

.green{ background:#16a34a; }
.blue{ background:#3b82f6; }
.gray{ background:#64748b; }

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

.stat h4{
    margin:0;
    font-size:13px;
    color:#6b7280;
    font-weight:500;
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

.top-bar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:10px;
    margin-bottom:15px;
    flex-wrap:wrap;
}

.search{
    padding:11px 14px;
    border:1px solid #d1d5db;
    border-radius:10px;
    width:260px;
    outline:none;
}

.search:focus{
    border-color:#3b82f6;
    box-shadow:0 0 0 3px rgba(59,130,246,.10);
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

th.right,td.right{ text-align:right; }
th.center,td.center{ text-align:center; }

tbody tr:hover{
    background:#f9fafb;
}

.low-row{
    background:#fff7ed;
}

.actions{
    display:flex;
    justify-content:flex-end;
    gap:8px;
}

.actions a{
    padding:7px 10px;
    border-radius:8px;
    color:#fff;
    font-size:12px;
    text-decoration:none;
    font-weight:600;
}

.edit{ background:#3b82f6; }
.delete{ background:#dc2626; }

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

.empty{
    text-align:center;
    padding:30px;
    color:#6b7280;
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
</style>

<div class="container">

    <div class="header">
        <h2>📦 Product Overview</h2>

        <div class="top-actions">
            <a href="/admin/products/create" class="btn green">+ Add Product</a>
            <button onclick="window.print()" class="btn gray">🖨 Print</button>
        </div>
    </div>

    {{-- PRODUCT STATS ONLY --}}
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
            <h4>Low Stock Items</h4>
            <h2>{{ $products->filter(fn($p) => $p->stock <= 10)->count() }}</h2>
        </div>

        <div class="stat">
            <h4>Available Items</h4>
            <h2>{{ $products->filter(fn($p) => $p->stock > 10)->count() }}</h2>
        </div>

    </div>

    {{-- PRODUCT TABLE --}}
    <div class="card">

        <div class="top-bar">
    <form method="GET" action="{{ url('/admin/products') }}">
        <input
            type="text"
            name="search"
            class="search"
            placeholder="🔍 Search product..."
            value="{{ request('search') }}"
        >
    </form>
</div>

        <table id="productTable">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Branch</th>
                    <th>Size</th>
                    <th>Color</th>
                    <th class="right">Price</th>
                    <th class="center">Stock</th>
                    <th class="center">Status</th>
                    <th class="right">Action</th>
                </tr>
            </thead>

            <tbody>
            @forelse($products as $p)
                <tr class="{{ $p->stock <= 10 ? 'low-row' : '' }}">
                    <td><strong>{{ $p->name }}</strong></td>
                    <td>{{ $p->branch->name ?? '-' }}</td>
                    <td>{{ $p->size }}</td>
                    <td>{{ $p->color }}</td>
                    <td class="right">₱{{ number_format($p->price, 2) }}</td>
                    <td class="center">{{ $p->stock }}</td>

                    <td class="center">
                        @if($p->stock <= 10)
                            <span class="badge low">Low Stock</span>
                        @else
                            <span class="badge ok">Available</span>
                        @endif
                    </td>

                    <td>
                        <div class="actions">
                            <a href="/admin/products/{{ $p->id }}/edit" class="edit">Edit</a>
                            <a href="/admin/products/{{ $p->id }}/delete"
                               class="delete"
                               onclick="return confirm('Delete this product?')">
                               Delete
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="empty">No products found</td>
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