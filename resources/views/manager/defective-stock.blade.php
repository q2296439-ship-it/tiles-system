@extends('layouts.manager')

@section('content')

<style>
.page{
    max-width:1280px;
    margin:auto;
}
.card{
    background:#fff;
    padding:28px;
    border-radius:16px;
    box-shadow:0 8px 20px rgba(0,0,0,.08);
}
.header{
    margin-bottom:20px;
}
.title{
    font-size:30px;
    font-weight:900;
    margin-bottom:6px;
}
.sub{
    color:#64748b;
    font-size:13px;
}
.grid{
    display:grid;
    grid-template-columns:380px 1fr;
    gap:22px;
}
.form-box,
.table-box{
    background:#f8fafc;
    border:1px solid #e5e7eb;
    border-radius:14px;
    padding:18px;
}
label{
    font-size:13px;
    color:#475569;
    margin-bottom:6px;
    display:block;
    font-weight:600;
}
input,select,textarea{
    width:100%;
    padding:11px 12px;
    margin-bottom:14px;
    border:1px solid #cbd5e1;
    border-radius:10px;
    font-size:14px;
    background:#fff;
}
textarea{
    resize:none;
}
.btn{
    width:100%;
    padding:12px;
    border:none;
    border-radius:10px;
    background:#dc2626;
    color:#fff;
    font-weight:700;
    font-size:14px;
    cursor:pointer;
}
.btn:hover{
    opacity:.95;
}
table{
    width:100%;
    border-collapse:separate;
    border-spacing:0;
}
th,td{
    padding:13px 12px;
    border-bottom:1px solid #eef2f7;
    font-size:14px;
    text-align:center;
    vertical-align:middle;
}
th{
    background:#f1f5f9;
    color:#475569;
    font-size:13px;
    font-weight:700;
}
.success{
    background:#dcfce7;
    color:#166534;
    padding:10px 12px;
    border-radius:10px;
    margin-bottom:14px;
}
.error{
    background:#fee2e2;
    color:#991b1b;
    padding:10px 12px;
    border-radius:10px;
    margin-bottom:14px;
}
.empty{
    color:#64748b;
    padding:20px;
}
.pagination-wrap{
    margin-top:16px;
    display:flex;
    justify-content:flex-end;
}
.pagination-wrap svg{
    width:18px;
    height:18px;
}
@media(max-width:992px){
    .grid{
        grid-template-columns:1fr;
    }
}
</style>

<div class="page">

<div class="card">

    <div class="header">
        <div class="title">🛠️ Defective Stock</div>
        <div class="sub">
            Write-off damaged / defective stocks from inventory
        </div>
    </div>

    @if(session('success'))
        <div class="success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="error">{{ session('error') }}</div>
    @endif

    <div class="grid">

        {{-- FORM --}}
        <div class="form-box">

            <form method="POST" action="{{ route('manager.defective.store') }}">
                @csrf

                <label>Select Branch</label>
                <select id="branchSelect">
                    <option value="">-- Select Branch --</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>

                <label>Product</label>
                <select name="product_id" id="productSelect" required>
                    <option value="">-- Select Product --</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}"
                            data-branch="{{ $product->branch_id }}">
                            {{ $product->name }} - {{ $product->size }}
                            (Stock: {{ $product->stock }})
                        </option>
                    @endforeach
                </select>

                <label>Quantity</label>
                <input type="number" name="quantity" min="1" required>

                <label>Reason</label>
                <textarea name="reason" rows="4" required></textarea>

                <button class="btn">Save Defective</button>

            </form>

        </div>

        {{-- TABLE --}}
        <div class="table-box">

            <table>
                <tr>
                    <th>Date</th>
                    <th>Product</th>
                    <th>Branch</th>
                    <th>Qty</th>
                    <th>Reason</th>
                </tr>

                @forelse($rows as $row)
                <tr>
                    <td>{{ $row->created_at->format('Y-m-d') }}</td>
                    <td>{{ $row->product->name ?? '-' }}</td>
                    <td>{{ $row->branch->name ?? '-' }}</td>
                    <td>{{ number_format($row->quantity) }}</td>
                    <td>{{ $row->reason }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="empty">
                        No defective records found.
                    </td>
                </tr>
                @endforelse

            </table>

            @if($rows->hasPages())
                <div class="pagination-wrap">
                    {{ $rows->links() }}
                </div>
            @endif

        </div>

    </div>

</div>

</div>

<script>
const branchSelect = document.getElementById('branchSelect');
const productSelect = document.getElementById('productSelect');

const allProducts = [];

for (let i = 1; i < productSelect.options.length; i++) {
    const opt = productSelect.options[i];

    allProducts.push({
        value: opt.value,
        text: opt.text,
        branch: opt.dataset.branch
    });
}

branchSelect.addEventListener('change', function () {

    const branchId = this.value;

    productSelect.innerHTML =
        '<option value="">-- Select Product --</option>';

    allProducts.forEach(item => {
        if (item.branch === branchId) {
            const option = document.createElement('option');
            option.value = item.value;
            option.textContent = item.text;
            productSelect.appendChild(option);
        }
    });
});
</script>

@endsection