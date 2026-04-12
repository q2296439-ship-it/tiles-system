<!DOCTYPE html>
<html>
<head>
    <title>Cashier POS</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', sans-serif;
            display: flex;
            height: 100vh;
            background: #f1f5f9;
        }

        .sidebar {
            width: 240px;
            background: linear-gradient(180deg, #0f172a, #020617);
            color: white;
            display: flex;
            flex-direction: column;
            height: 100vh;
        }

        .sidebar-menu {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
        }

        .sidebar h2 {
            margin-bottom: 20px;
            font-size: 20px;
        }

        .sidebar p {
            font-size: 11px;
            color: #94a3b8;
            margin: 18px 0 6px;
            letter-spacing: 1px;
        }

        .sidebar a {
            display: block;
            padding: 12px;
            color: #cbd5f5;
            text-decoration: none;
            border-radius: 10px;
            margin-bottom: 6px;
            transition: 0.2s;
            font-size: 14px;
        }

        .sidebar a:hover {
            background: #1e293b;
            color: white;
        }

        .active {
            background: #22c55e;
            color: white !important;
            font-weight: bold;
        }

        hr {
            border: none;
            border-top: 1px solid #334155;
            margin: 15px 0;
        }

        .logout {
            padding: 15px;
        }

        .logout button {
            width: 100%;
            padding: 12px;
            background: #ef4444;
            border: none;
            border-radius: 10px;
            color: white;
            cursor: pointer;
        }

        .sidebar-menu::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-menu::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 10px;
        }

        .content {
            flex: 1;
            padding: 25px;
            overflow-y: auto;
        }

        .cart {
            width: 320px;
            background: linear-gradient(180deg, #1e293b, #020617);
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
        }

        .cart h2 {
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-menu">

        <h2>💰 POS</h2>

        <p>MAIN</p>
        <a href="{{ url('/cashier') }}" 
           class="{{ request()->is('cashier') ? 'active' : '' }}">
            🧾 New Sale
        </a>

        <p>SALES</p>
        <a href="#">🧾 Add Collection Receipt</a>
        <a href="#">📊 Collection Today</a>
        <a href="#">💰 DCCR</a>
        <a href="#">🏦 Deposit</a>

        <p>INVENTORY</p>
        <a href="#">📦 Inventory Stock</a>

        <!-- ✅ FIXED -->
        <a href="{{ route('cashier.transfer.in') }}" 
           class="{{ request()->is('cashier/transfer-in*') ? 'active' : '' }}">
            ⬇ Transfer In
        </a>

        <a href="#">⬆ Transfer Out</a>

        <hr>

        <p>ACCOUNT</p>
        <a href="#">🔑 Change Password</a>

    </div>

    <div class="logout">
        <form method="POST" action="{{ url('/logout') }}">
            @csrf
            <button>🚪 Logout</button>
        </form>
    </div>
</div>

<!-- CONTENT -->
<div class="content">

    <h3>Transfer In Request</h3>

    <form action="{{ route('cashier.transfer.in.store') }}" method="POST">
        @csrf

        <table border="1" width="100%" cellpadding="10">
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Qty</th>
                </tr>
            </thead>
            <tbody>
                @foreach($products as $index => $product)
                <tr>
                    <td>
                        {{ $product->name }}
                        <input type="hidden" name="items[{{ $index }}][product_id]" value="{{ $product->id }}">
                    </td>
                    <td>
                        <input type="number" name="items[{{ $index }}][qty]" min="0" value="0">
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <br>

        <label>From Branch</label>
        <select name="from_branch_id" required>
            <option value="">Select Branch</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">{{ $branch->name }}</option>
            @endforeach
        </select>

        <br><br>

        <button type="submit">Submit Request</button>
    </form>

</div>

<!-- CART -->
<div class="cart">
    <h2>Cart Area</h2>
</div>

</body>
</html>