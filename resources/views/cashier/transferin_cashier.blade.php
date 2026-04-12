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

        <!-- PAGINATION -->
        <div style="display:flex; justify-content:center; gap:10px; margin-top:15px;">
            <button id="prevBtn" style="padding:8px 15px; background:#64748b; color:white; border:none; border-radius:6px;">
                ⬅ Back
            </button>

            <button id="nextBtn" style="padding:8px 15px; background:#0ea5e9; color:white; border:none; border-radius:6px;">
                Next ➡
            </button>
        </div>

        <!-- CART -->
        <div style="margin-top:25px; background:white; padding:20px; border-radius:12px;">
            <h4>🛒 Request Cart</h4>

            <form action="{{ route('cashier.transfer.in.store') }}" method="POST">
                @csrf

                <div id="cart-items"></div>

                <hr>

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

                <!-- 🔥 FIXED BUTTON -->
                <button type="submit" style="margin-top:10px; width:100%; padding:12px; background:#22c55e; border:none; border-radius:8px; color:white;">
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
let currentPage = 1;
let perPage = 6;

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

// 🔥 FIXED RENDER CART
function renderCart() {
    let container = document.getElementById('cart-items');
    container.innerHTML = '';

    cart.forEach((item, index) => {

        let div = document.createElement('div');

        div.style = "margin-bottom:10px; padding:10px; background:#f1f5f9; border-radius:8px;";

        div.innerHTML = `
            <strong>${item.name}</strong>
            <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
            <input type="number" name="items[${index}][qty]" value="${item.qty}" min="1" style="width:60px; margin-left:10px;">
            <button type="button" onclick="removeItem(${index})">❌</button>
        `;

        container.appendChild(div);
    });
}

// REMOVE
function removeItem(index){
    cart.splice(index,1);
    renderCart();
}

// 🔥 PREVENT EMPTY SUBMIT
document.querySelector("form").addEventListener("submit", function(e) {
    if (cart.length === 0) {
        e.preventDefault();
        alert("No items in cart!");
    }
});

// PAGINATION
function showPage() {
    let cards = document.querySelectorAll('.product-card');

    let start = (currentPage - 1) * perPage;
    let end = start + perPage;

    cards.forEach((card, index) => {
        card.style.display = (index >= start && index < end) ? 'block' : 'none';
    });

    document.getElementById('prevBtn').disabled = currentPage === 1;
    document.getElementById('nextBtn').disabled = end >= cards.length;
}

// NEXT
document.getElementById('nextBtn').addEventListener('click', function() {
    currentPage++;
    showPage();
});

// BACK
document.getElementById('prevBtn').addEventListener('click', function() {
    if (currentPage > 1) {
        currentPage--;
        showPage();
    }
});

// SEARCH
document.getElementById('searchInput').addEventListener('keyup', function() {
    let value = this.value.toLowerCase();
    let cards = document.querySelectorAll('.product-card');

    if (value === "") {
        currentPage = 1;
        showPage();
        return;
    }

    document.getElementById('nextBtn').style.display = 'none';
    document.getElementById('prevBtn').style.display = 'none';

    cards.forEach(card => {
        let name = card.dataset.name;
        let branch = card.dataset.branch;

        if (name.includes(value) || branch.includes(value)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });
});

// FILTER
document.getElementById('branchFilter').addEventListener('change', function() {
    let selected = this.value;
    let cards = document.querySelectorAll('.product-card');

    cards.forEach(card => {
        let branchId = card.dataset.branchId;

        if (selected === "" || Number(branchId) === Number(selected)) {
            card.style.display = 'block';
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('nextBtn').style.display = 'none';
    document.getElementById('prevBtn').style.display = 'none';
});

// INIT
document.addEventListener('DOMContentLoaded', function() {
    showPage();
});
</script>
@endsection