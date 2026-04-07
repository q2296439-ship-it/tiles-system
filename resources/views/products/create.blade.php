<!DOCTYPE html>
<html>
<head>
    <title>Add Product</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f1f5f9;
            margin: 0;
            padding: 40px;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }

        h2 {
            margin-bottom: 20px;
        }

        label {
            font-size: 13px;
            color: #475569;
            display: block;
            margin-bottom: 5px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
        }

        .btn {
            background: #22c55e;
            color: white;
            padding: 10px;
            border: none;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
        }

        .back {
            display: inline-block;
            margin-bottom: 15px;
            font-size: 13px;
            text-decoration: none;
            color: #3b82f6;
        }
    </style>
</head>

<body>

<div class="card">

    <!-- ✅ FIXED ROUTE -->
    <a href="/admin/products" class="back">← Back to Products</a>

    <h2>➕ Add Product</h2>

    @if($errors->any())
        <div class="error">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- ✅ FIXED FORM ACTION -->
    <form method="POST" action="/admin/products">
        @csrf

        <label>SKU</label>
        <input type="text" name="sku" required>

        <label>Category</label>
        <input type="text" name="category" required>

        <label>Product Name</label>
        <input type="text" name="name" required>

        <label>Size</label>
        <input type="text" name="size">

        <label>Color</label>
        <input type="text" name="color">

        <label>Price</label>
        <input type="number" step="0.01" name="price" required>

        <label>Stock</label>
        <input type="number" name="stock" required>

        <label>Low Stock Threshold</label>
        <input type="number" name="low_stock_threshold" value="10">

        <!-- 🔥 NEW: BRANCH DROPDOWN -->
        <label>Select Branch</label>
        <select name="branch_id" required>
            <option value="">-- Select Branch --</option>
            @foreach($branches as $branch)
                <option value="{{ $branch->id }}">
                    {{ $branch->name }}
                </option>
            @endforeach
        </select>

        <button type="submit" class="btn">Save Product</button>
    </form>

</div>

</body>
</html>