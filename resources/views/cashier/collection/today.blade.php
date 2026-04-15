@extends(
    strtolower(auth()->user()->role) === 'admin'
        ? 'layouts.admin'
        : (strtolower(auth()->user()->role) === 'manager'
            ? 'layouts.manager'
            : 'layouts.cashier')
)

@section('content')

@php
    $isAdmin = strtolower(auth()->user()->role) === 'admin';
@endphp

<style>
.page{
    max-width:1200px;
    margin:auto;
}
.header-card,.card,.table-card{
    background:#ffffff;
    border-radius:18px;
    padding:22px;
    box-shadow:0 8px 20px rgba(0,0,0,.06);
}
.header-card{ margin-bottom:20px; }
.title{
    font-size:34px;
    font-weight:900;
    color:#0f172a;
}
.sub{
    color:#64748b;
    margin-top:4px;
}
.stats{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(220px,1fr));
    gap:16px;
    margin-bottom:20px;
}
.label{
    font-size:13px;
    color:#64748b;
    margin-bottom:10px;
}
.value{
    font-size:34px;
    font-weight:900;
    color:#0f172a;
}
.value-green{ color:#16a34a; }

.topbar{
    display:flex;
    justify-content:space-between;
    align-items:center;
    gap:15px;
    flex-wrap:wrap;
    margin-bottom:15px;
}
.right-tools{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    align-items:center;
}
.search,.date-input,.select-filter{
    padding:10px 14px;
    border:1px solid #cbd5e1;
    border-radius:10px;
}
.search{ min-width:260px; }

.btn{
    border:none;
    padding:10px 14px;
    border-radius:10px;
    color:#fff;
    font-weight:700;
    cursor:pointer;
    text-decoration:none;
    display:inline-block;
}
.btn-blue{ background:#2563eb; }
.btn-green{ background:#16a34a; }
.btn-red{ background:#dc2626; }

table{
    width:100%;
    border-collapse:collapse;
}
th,td{
    padding:12px;
    border-bottom:1px solid #e5e7eb;
    text-align:left;
    font-size:14px;
    vertical-align:top;
}
th{
    color:#475569;
    background:#f8fafc;
}
.badge{
    padding:6px 10px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
}
.badge-saved{
    background:#dcfce7;
    color:#166534;
}
.badge-cancel{
    background:#fee2e2;
    color:#991b1b;
}
.badge-return{
    background:#dbeafe;
    color:#1d4ed8;
}
.empty{
    text-align:center;
    padding:35px;
    color:#94a3b8;
}
.item-line{ margin-bottom:4px; }
.qty-badge{
    display:inline-block;
    background:#eff6ff;
    color:#1d4ed8;
    padding:2px 8px;
    border-radius:999px;
    font-size:12px;
    font-weight:700;
}
.pagination-wrap{
    margin-top:20px;
    display:flex;
    justify-content:center;
}
.pagination-wrap nav{
    display:flex;
    gap:6px;
    flex-wrap:wrap;
}
.pagination-wrap span,
.pagination-wrap a{
    padding:8px 12px;
    border-radius:10px;
    border:1px solid #e5e7eb;
    text-decoration:none;
    color:#0f172a;
    font-size:14px;
}
.pagination-wrap .active span{
    background:#2563eb;
    color:#fff;
    border-color:#2563eb;
}
</style>

<div class="page">

<div class="header-card">
    <div style="display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;">

        <div>
            <div class="title">📊 Collection Report</div>
            <div class="sub">View today or previous collection receipts</div>
        </div>

        <div class="right-tools">

            <form method="GET" action="" style="display:flex; gap:10px; flex-wrap:wrap;">

                <input type="date"
                       name="date"
                       class="date-input"
                       value="{{ request('date', date('Y-m-d')) }}">

                <select name="status" class="select-filter">
                    <option value="all" {{ request('status') == 'all' || request('status') == null ? 'selected' : '' }}>All Status</option>
                    <option value="saved" {{ request('status') == 'saved' ? 'selected' : '' }}>Saved</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    <option value="returned" {{ request('status') == 'returned' ? 'selected' : '' }}>Returned</option>
                </select>

                <button type="submit" class="btn btn-blue">Filter</button>
            </form>

            <input type="text" id="searchInput" class="search"
                   placeholder="🔍 Search customer / receipt no...">

            <a href="{{ route('cashier.collection.export.excel', ['date' => request('date'),'status' => request('status')]) }}"
               class="btn btn-green">📗 Excel</a>

           @php
    $role = strtolower(auth()->user()->role);

    if ($role === 'admin') {
        $pdfRoute = route('admin.collection.export.pdf', [
            'date' => request('date'),
            'status' => request('status')
        ]);
    } else {
        $pdfRoute = route('cashier.collection.export.pdf', [
            'date' => request('date'),
            'status' => request('status')
        ]);
    }
@endphp

<a href="{{ $pdfRoute }}"
   target="_blank"
   rel="noopener noreferrer"
   class="btn btn-red">📄 PDF</a>

</div>

</div>
</div>

<div class="stats">
    <div class="card">
        <div class="label">Total Receipts</div>
        <div class="value">{{ $collections->total() }}</div>
    </div>

    <div class="card">
        <div class="label">Total Collection</div>
        <div class="value value-green">₱{{ number_format($total, 2) }}</div>
    </div>
</div>

<div class="table-card">

    <div class="topbar">
        <h3 style="margin:0;">Receipt List</h3>
    </div>

    <div style="overflow:auto;">
        <table id="collectionTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Receipt No</th>
                    <th>Date</th>

                    @if($isAdmin)
                        <th>Branch</th>
                    @endif

                    <th>Customer</th>
                    <th>Products</th>
                    <th>Qty</th>
                    <th>Total</th>
                    <th>Cashier</th>
                    <th>Time</th>
                    <th>Status</th>
                </tr>
            </thead>

            <tbody>
            @forelse($collections as $row)

                @php
                    $status = strtolower($row->record_type ?? $row->status ?? 'saved');
                @endphp

                <tr data-status="{{ $status }}">
                    <td>{{ ($collections->firstItem() ?? 0) + $loop->index }}</td>

                    <td>
                        @if($status == 'returned')
                            {{ $row->receipt_no }}
                        @else
                            {{ $row->display_receipt_no ?? $row->receipt_no }}
                        @endif
                    </td>

                    <td>{{ \Carbon\Carbon::parse($row->receipt_date)->format('M d, Y') }}</td>

                    @if($isAdmin)
                        <td>{{ $row->branch->name ?? '-' }}</td>
                    @endif

                    <td>{{ $row->customer_name }}</td>

                    <td>
                        @foreach($row->items as $item)
                            <div class="item-line">{{ $item->description }}</div>
                        @endforeach
                    </td>

                    <td>
                        @foreach($row->items as $item)
                            <div class="item-line">
                                <span class="qty-badge">{{ $item->qty }}</span>
                            </div>
                        @endforeach
                    </td>

                    <td>₱{{ number_format($row->total_amount, 2) }}</td>
                    <td>{{ $row->user->name ?? 'Cashier' }}</td>
                    <td>{{ $row->created_at->format('h:i A') }}</td>

                    <td>
                        @if($status == 'cancelled')
                            <span class="badge badge-cancel">Cancelled</span>
                        @elseif($status == 'returned')
                            <span class="badge badge-return">Returned</span>
                        @else
                            <span class="badge badge-saved">Saved</span>
                        @endif
                    </td>
                </tr>

            @empty
                <tr>
                    <td colspan="{{ $isAdmin ? '11' : '10' }}" class="empty">No collection found.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if($collections->hasPages())
        <div class="pagination-wrap">
            {{ $collections->links() }}
        </div>
    @endif

</div>

</div>

<script>
const searchInput = document.getElementById('searchInput');

searchInput.addEventListener('keyup', function () {
    let search = this.value.toLowerCase();
    let rows = document.querySelectorAll('#collectionTable tbody tr');

    rows.forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(search)
            ? ''
            : 'none';
    });
});
</script>

@endsection