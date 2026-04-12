@extends('layouts.cashier')

@section('content')
<div style="display:flex; gap:20px;">

    <!-- LEFT SIDE -->
    <div style="flex:2;">
        <h3 style="margin-bottom:15px;">Transfer In Request</h3>

        <input type="text" placeholder="🔍 Search product..." 
               style="width:100%; padding:10px; border-radius:8px; border:1px solid #ccc; margin-bottom:15px;">

        <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:15px;">
            @foreach($products as $product)
            <div class="product-card"
                 data-id="{{ $product->id }}"
                 data-name="{{ $product->name }}"
                 style="background:white; padding:15px; border-radius:12px; cursor:pointer; box-shadow:0 2px 6px rgba(0,0,0,0.1);">

                <strong>{{ $product->name }}</strong>
                <p style="color:green;">₱{{ number_format($product->price,2) }}</p>
                <small>Stock: {{ $product->stock }}</small>
            </div>
            @endforeach
        </div>
    </div>

    <!-- RIGHT SIDE -->
    <div style="flex:1; background:#0f172a; color:white; padding:20px; border-radius:12px;">

        <h4>🛒 Request Cart</h4>

        <form action="{{ route('cashier.transfer.in.store') }}" method="POST">
            @csrf

            <div id="cart-items" style="margin-top:15px;"></div>

            <hr style="margin:15px 0; border-color:#334155;">

            <label>From Branch</label>
            <select name="from_branch_id" style="width:100%; padding:10px; border-radius:8px; margin-bottom:10px;">
                <option value="">Select Branch</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>

            <label>Notes</label>
            <textarea name="notes" style="width:100%; padding:10px; border-radius:8px;"></textarea>

            <button style="margin-top:15px; width:100%; padding:12px; background:#22c55e; border:none; border-radius:10px; color:white;">
                Submit Request
            </button>
        </form>

    </div>

</div>
@endsection


@section('scripts')
<script>
let cart = [];

// CLICK PRODUCT
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

// RENDER CART
function renderCart() {
    let container = document.getElementById('cart-items');
    container.innerHTML = '';

    cart.forEach((item, index) => {
        container.innerHTML += `
            <div style="margin-bottom:10px; background:#1e293b; padding:10px; border-radius:8px;">
                <strong>${item.name}</strong>
                <br>
                <input type="number" name="items[${index}][qty]" value="${item.qty}" min="1"
                       style="width:60px; margin-top:5px;">
                <input type="hidden" name="items[${index}][product_id]" value="${item.id}">
                <button type="button" onclick="removeItem(${index})" style="float:right;">❌</button>
            </div>
        `;
    });
}

// REMOVE
function removeItem(index){
    cart.splice(index,1);
    renderCart();
}
</script>
@endsection