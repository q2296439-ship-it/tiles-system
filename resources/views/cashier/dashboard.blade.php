<!DOCTYPE html>
<html>
<head>
    <title>Cashier POS</title>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f1f5f9;
        }

        .container {
            display: flex;
            height: 100vh;
        }

        .sidebar {
            width: 220px;
            background: #0f172a;
            color: white;
            padding: 20px;
        }

        .sidebar h2 {
            margin-bottom: 20px;
        }

        .sidebar a {
            display: block;
            color: #cbd5f5;
            margin-bottom: 10px;
            text-decoration: none;
        }

        .main {
            flex: 1;
            padding: 20px;
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .search input {
            width: 100%;
            padding: 10px;
            border-radius: 10px;
            border: 1px solid #ddd;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
            margin-top: 15px;
        }

        .product {
            background: white;
            padding: 15px;
            border-radius: 12px;
            cursor: pointer;
            text-align: center;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08);
        }

        .product:hover {
            transform: scale(1.05);
        }

        .cart {
            width: 320px;
            background: #1e293b;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .cart-items {
            flex: 1;
            overflow-y: auto;
        }

        .item {
            background: #334155;
            padding: 10px;
            border-radius: 8px;
            margin-bottom: 10px;
        }

        .qty input {
            width: 60px;
            text-align: center;
            margin-top: 5px;
        }

        .total {
            font-size: 20px;
            margin: 10px 0;
        }

        input {
            padding: 10px;
            border-radius: 8px;
            border: none;
            margin-bottom: 10px;
        }

        button.pay {
            padding: 15px;
            background: #22c55e;
            border: none;
            border-radius: 10px;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }

        button.pay:disabled {
            background: gray;
        }
    </style>
</head>

<body>

<div class="container">

    <!-- SIDEBAR -->
    <div class="sidebar">
        <h2>💰 POS</h2>

        <a href="#">New Sale</a>
        <a href="#">Sales History</a>
        <a href="#">Products</a>

        <form method="POST" action="/logout">
            @csrf
            <button style="margin-top:20px;">Logout</button>
        </form>
    </div>

    <!-- MAIN -->
    <div class="main">

        <div class="topbar">
            <h2>Cashier Dashboard</h2>
            <div>👤 {{ auth()->user()->username }}</div>
        </div>

        <div class="search">
            <input type="text" placeholder="Search product...">
        </div>

        <div class="grid">
            @foreach($products as $product)
            <div class="product"
                onclick="addToCart({{ $product->id }}, '{{ $product->name }}', {{ $product->price }})">

                <h4>{{ $product->name }}</h4>
                <p>₱{{ number_format($product->price, 2) }}</p>
                <small>Stock: {{ $product->stock }}</small>
            </div>
            @endforeach
        </div>

    </div>

    <!-- CART -->
    <div class="cart">

        <h2>🛒 Cart</h2>

        <div class="cart-items" id="cart"></div>

        <div class="total">
            Total: ₱<span id="total">0.00</span>
        </div>

        <input type="number" id="cash" placeholder="Enter cash">

        <div>Change: ₱<span id="change">0.00</span></div>

        <button class="pay" id="payBtn" onclick="checkout()" disabled>PAY</button>

    </div>

</div>

<script>
let cart = [];

function addToCart(id, name, price) {
    let item = cart.find(i => i.id === id);

    if (item) {
        item.qty++;
    } else {
        cart.push({ id, name, price, qty: 1 });
    }

    render();
}

function updateQty(id, value) {
    let item = cart.find(i => i.id === id);
    let qty = parseInt(value);

    if (qty <= 0 || isNaN(qty)) {
        cart = cart.filter(i => i.id !== id);
    } else {
        item.qty = qty;
    }

    render();
}

function render() {
    let html = '';
    let total = 0;

    cart.forEach(item => {
        total += item.price * item.qty;

        html += `
            <div class="item">
                <div>${item.name}</div>
                <div>₱${(item.price * item.qty).toFixed(2)}</div>
                <div class="qty">
                    <input type="number" min="1" value="${item.qty}" 
                        onchange="updateQty(${item.id}, this.value)">
                </div>
            </div>
        `;
    });

    document.getElementById('cart').innerHTML = html;
    document.getElementById('total').innerText = total.toFixed(2);

    checkPay();
}

document.getElementById('cash').addEventListener('input', function () {
    let cash = parseFloat(this.value) || 0;
    let total = parseFloat(document.getElementById('total').innerText);

    let change = cash - total;
    document.getElementById('change').innerText =
        change > 0 ? change.toFixed(2) : '0.00';

    checkPay();
});

function checkPay() {
    let cash = parseFloat(document.getElementById('cash').value) || 0;
    let total = parseFloat(document.getElementById('total').innerText);

    document.getElementById('payBtn').disabled = !(cash >= total && total > 0);
}

function checkout() {
    fetch('/cashier/checkout', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            items: cart,
            total: document.getElementById('total').innerText
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('Sale completed!');

            // 🔥 AUTO REFRESH PARA UPDATE STOCK
            location.reload();
        } else {
            alert(data.message);
        }
    });
}
</script>

</body>
</html>