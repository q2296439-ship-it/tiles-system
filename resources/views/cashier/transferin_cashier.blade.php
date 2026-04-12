@extends('layouts.cashier')

@section('content')
<div style="display:flex; gap:20px;">

    <!-- LEFT SIDE -->
    <div style="flex:2;">

        <h3>Transfer In Request</h3>

        <!-- 🔍 SEARCH -->
        <input type="text" id="searchInput" placeholder="🔍 Search product or branch..." 
               style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; margin-bottom:15px;">

        <!-- PRODUCTS -->
        <div id="productContainer" style="display:grid; grid-template-columns:repeat(3,1fr); gap:15px;">
            @foreach($products as $product)
            <div class="product-card"
                 data-id="{{ $product->id }}"
                 data-name="{{ strtolower($product->name) }}"
                 data-branch="{{ strtolower($product->branch->name ?? '') }}"
                 data-branch-id="{{ $product->branch_id }}"
                 style="background:white; padding:15px; border-radius:12px; cursor:pointer; box-shadow:0 2px 6px rgba(0,0,0,0.1);">

                <strong>{{ $product->name }}</strong>
                <p style="color:green;">₱{{ number_format($product->price,2) }}</p>

                <small>Stock: {{ $product->stock }}</small><br>
                <small style="color:#64748b;">
                    Branch: {{ $product->branch->name ?? 'N/A' }}
                </small>
            </div>
            @endforeach
        </div>

        <!-- CART -->
        <div style="margin-top:25px; background:white; padding:20px; border-radius:12px;">
            <h4>🛒 Request Cart</h4>

            <form action="{{ route('cashier.transfer.in.store') }}" method="POST">
                @csrf

                <div id="cart-items"></div>

                <hr>

                <!-- 🔥 FROM / TO -->
                <div style="display:flex; gap:10px;">
                    <div style="flex:1;">
                        <label>From Branch</label>
                        <select id="branchFilter" name="from_branch_id" style="width:100%; padding:8px;">
                            <option value="">All Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div style="flex:1;">
                        <label>To Branch</label>
                        <select name="to_branch_id" style="width:100%; padding:8px;">
                            <option value="{{ auth()->user()->branch_id }}">
                                {{ auth()->user()->branch->name ?? 'My Branch' }}
                            </option>
                        </select>
                    </div>
                </div>

                <br>

                <label>Notes</label>
                <textarea name="notes" style="width:100%; padding:10px;"></textarea>

                <button style="margin-top:10px; width:100%; padding:12px; background:#22c55e; border:none; border-radius:8px; color:white;">
                    Submit Request
                </button>
            </form>
        </div>

    </div>

</div>
@endsection


@section('cart')
<h3>📋 Requests</h3>

@forelse($requests as $req)
<div style="margin-bottom:15px; background:#1e293b; padding:10px; border-radius:10px;">

    <strong>
        {{ $req->from_branch->name ?? 'N/A' }} → {{ $req->branch->name ?? 'N/A' }}
    </strong>
    
    <br>
    <small>{{ $req->created_at->format('M d, Y h:i A') }}</small><br>

    <span style="color:yellow;">
        {{ ucfirst($req->status) }}
    </span>

    <div style="margin-top:5px;">
        {{ $req->product->name }} ({{ $req->quantity }})
    </div>

</div>
@empty
    <p>No requests yet</p>
@endforelse
@endsection


@section('scripts')
<script>
let cart = [];

// ADD TO CART
document.querySelectorAll('.product-card').forEach(card => {
    card.addEventListener('click', () => {
        let id = card.dataset.id;
        let name = card.dataset.name;

        let existing = cart.find(i => i.id == id);

        if (existing) {
            existing.qty++;
        } else {
            cart.push({id, name, qty:1});
        }

        renderCart();
    });
});

function renderCart() {
    let container = document.getElementById('cart-items');
    container.innerHTML = '';

    cart.forEach((item, index) => {
        container.innerHTML += `
            <div style="margin-bottom:10px; padding:10px; background:#f1f5f9; border-radius:8px;">
                <strong>${item.name}</strong>
                <input type="number" name="items[${index}][qty]" value="${item.qty}" min="1" style="width:60px;">
                <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                <button type="button" onclick="removeItem(${index})">❌</button>
            </div>
        `;
    });
}

function removeItem(index){
    cart.splice(index,1);
    renderCart();
}

// 🔍 SEARCH
document.getElementById('searchInput').addEventListener('keyup', function() {
    let value = this.value.toLowerCase();

    document.querySelectorAll('.product-card').forEach(card => {
        let name = card.dataset.name;
        let branch = card.dataset.branch;

        if (name.includes(value) || branch.includes(value)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

// 🔥 DROPDOWN FILTER (MAIN FEATURE)
document.getElementById('branchFilter').addEventListener('change', function() {
    let selected = this.value;

    document.querySelectorAll('.product-card').forEach(card => {
        let branchId = card.dataset.branchId;

        if (selected === "" || branchId === selected) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});
</script>
@endsection