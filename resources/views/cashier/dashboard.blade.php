@extends('layouts.cashier')

@section('content')

<style>
    .topbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .search input {
        width: 100%;
        padding: 14px;
        border-radius: 14px;
        border: 1px solid #ddd;
        background: #fff;
    }

    .pos-wrapper {
        background: #ffffff;
        padding: 20px;
        border-radius: 18px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 18px;
        margin-top: 15px;
    }

    .product {
        background: #f9fafb;
        padding: 18px;
        border-radius: 16px;
        text-align: center;
        cursor: pointer;
        border: 2px solid transparent;
        transition: 0.2s;
    }

    .product:hover {
        border-color: #22c55e;
        transform: translateY(-4px);
    }

    .product h4 {
        margin: 8px 0;
        font-size: 15px;
        font-weight: 600;
    }

    .size {
        font-size: 12px;
        color: #64748b;
    }

    .price {
        color: #22c55e;
        font-weight: bold;
        margin-top: 5px;
    }

    .stock {
        font-size: 12px;
        margin-top: 6px;
    }

    .out {
        opacity: 0.4;
        pointer-events: none;
    }
</style>

<div class="topbar">
    <h2>🧾 New Sale</h2>
    <div>👤 {{ auth()->user()->username }}</div>
</div>

<div class="pos-wrapper">

    <div class="search">
        <input type="text" placeholder="🔍 Search product...">
    </div>

    <div class="grid">
        @foreach($products as $product)
        <div class="product {{ $product->stock <= 0 ? 'out' : '' }}"
            onclick='addToCart({{ $product->id }}, @json($product->name), {{ $product->price }})'>

            <h4>{{ $product->name }}</h4>
            <div class="size">{{ $product->size }}</div>

            <div class="price">₱{{ number_format($product->price,2) }}</div>

            <div class="stock" style="color: {{ $product->stock <= 5 ? 'red' : '#64748b' }}">
                Stock: {{ $product->stock }}
            </div>

        </div>
        @endforeach
    </div>

</div>

@endsection


@section('cart')

<style>
    .cart-items {
        flex: 1;
        overflow-y: auto;
        margin-bottom: 10px;
    }

    .item {
        background: #334155;
        padding: 14px;
        border-radius: 12px;
        margin-bottom: 10px;
    }

    .item-name {
        font-weight: bold;
        margin-bottom: 5px;
    }

    .qty-control {
        display: flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
    }

    .qty-control button {
        padding: 4px 8px;
        border: none;
        border-radius: 6px;
        cursor: pointer;
    }

    .qty-control input {
        width: 55px;
        text-align: center;
        border-radius: 6px;
        border: none;
    }

    .total {
        font-size: 20px;
        margin: 10px 0;
        font-weight: bold;
    }

    #cash {
        width: 100%;
        padding: 12px;
        border-radius: 10px;
        border: none;
        margin-bottom: 10px;
    }

    .pay {
        width: 100%;
        padding: 15px;
        border-radius: 12px;
        background: #22c55e;
        color: white;
        font-weight: bold;
    }

    /* 🔥 PRO MODAL */
    .modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.6);
        backdrop-filter: blur(5px);
        justify-content: center;
        align-items: center;
        z-index: 9999;
    }

    .modal-box {
        background: #fff;
        padding: 35px;
        border-radius: 18px;
        width: 340px;
        text-align: center;
        animation: pop 0.25s ease;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }

    @keyframes pop {
        from { transform: scale(0.8); opacity: 0; }
        to { transform: scale(1); opacity: 1; }
    }

    .icon {
        width: 60px;
        height: 60px;
        margin: auto;
        background: #22c55e;
        color: white;
        font-size: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
    }

    .actions {
        margin-top: 20px;
        display: flex;
        gap: 10px;
    }

    .actions button {
        flex: 1;
        padding: 12px;
        border-radius: 10px;
        border: none;
        font-weight: bold;
        cursor: pointer;
    }

    .btn-print {
        background: #22c55e;
        color: white;
    }

    .btn-close {
        background: #64748b;
        color: white;
    }
</style>

<h2>🛒 Cart</h2>

<div class="cart-items" id="cart"></div>

<div class="total">
    Total: ₱<span id="total">0.00</span>
</div>

<input type="number" id="cash" placeholder="Enter exact cash" oninput="calculateChange()">

<div>Change: ₱<span id="change">0.00</span></div>

<button class="pay" id="payBtn" onclick="checkout()" disabled>
    💳 PAY
</button>

<!-- 🔥 PRO MODAL -->
<div id="successModal" class="modal-overlay">

    <div class="modal-box">

        <div class="icon">✔</div>

        <h2>Payment Successful</h2>
        <p>Transaction completed successfully</p>

        <div class="actions">
            <button class="btn-print" onclick="printReceipt()">🖨 Print</button>
            <button class="btn-close" onclick="closeModal()">Close</button>
        </div>

    </div>

</div>

@endsection


@section('scripts')

<script>
let cart = [];

function addToCart(id, name, price){
    let item = cart.find(i => i.id === id);

    if(item){
        item.qty++;
    } else {
        cart.push({id, name, price, qty:1});
    }

    renderCart();
}

function renderCart(){
    let html = '';
    let total = 0;

    cart.forEach((item, index)=>{
        total += item.price * item.qty;

        html += `
            <div class="item">
                <div class="item-name">${item.name}</div>
                ₱${item.price}

                <div class="qty-control">
                    <button onclick="decreaseQty(${index})">-</button>
                    <input type="number" value="${item.qty}" min="1"
                        onchange="updateQty(${index}, this.value)">
                    <button onclick="increaseQty(${index})">+</button>
                </div>
            </div>
        `;
    });

    document.getElementById('cart').innerHTML = html;
    document.getElementById('total').innerText = total.toFixed(2);

    calculateChange();
}

function increaseQty(index){ cart[index].qty++; renderCart(); }
function decreaseQty(index){
    if(cart[index].qty > 1){ cart[index].qty--; }
    else { cart.splice(index, 1); }
    renderCart();
}

function updateQty(index, value){
    let qty = parseInt(value);
    if(qty <= 0 || isNaN(qty)){ cart.splice(index, 1); }
    else { cart[index].qty = qty; }
    renderCart();
}

function calculateChange(){
    let cash = parseFloat(document.getElementById('cash').value) || 0;
    let total = parseFloat(document.getElementById('total').innerText) || 0;

    let change = cash - total;
    document.getElementById('change').innerText = change.toFixed(2);

    let payBtn = document.getElementById('payBtn');

    if(cash !== total || total === 0){
        payBtn.disabled = true;
        payBtn.style.opacity = 0.5;
    } else {
        payBtn.disabled = false;
        payBtn.style.opacity = 1;
    }
}

function checkout(){
    let cash = parseFloat(document.getElementById('cash').value) || 0;
    let total = parseFloat(document.getElementById('total').innerText) || 0;

    if(cart.length === 0){ alert("No items in cart"); return; }
    if(cash !== total){ alert("Cash must be exact amount!"); return; }

    fetch('/cashier/checkout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ items: cart, total: total })
    })
    .then(res => res.json())
    .then(data => {
        if(data.success){
            showSuccessModal();
            cart = [];
            renderCart();
            updateProducts(data.products);
        } else {
            alert(data.message);
        }
    })
    .catch(err => {
        console.error(err);
        alert("Checkout failed");
    });
}

function showSuccessModal(){
    document.getElementById('successModal').style.display = 'flex';
}

function closeModal(){
    document.getElementById('successModal').style.display = 'none';
}

function printReceipt(){
    window.print();
}

function updateProducts(products){
    let grid = document.querySelector('.grid');
    let html = '';

    products.forEach(product => {
        html += `
            <div class="product ${product.stock <= 0 ? 'out' : ''}"
                onclick='addToCart(${product.id}, "${product.name}", ${product.price})'>
                <h4>${product.name}</h4>
                <div class="size">${product.size ?? ''}</div>
                <div class="price">₱${parseFloat(product.price).toFixed(2)}</div>
                <div class="stock" style="color:${product.stock <= 5 ? 'red' : '#64748b'}">
                    Stock: ${product.stock}
                </div>
            </div>
        `;
    });

    grid.innerHTML = html;
}
</script>

@endsection