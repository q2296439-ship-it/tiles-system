<!DOCTYPE html>
<html>
<head>
    <title>Edit Product</title>

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

        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 14px;
        }

        input:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .btn {
            background: #3b82f6;
            color: white;
            padding: 10px;
            border: none;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
        }

        .btn:hover {
            background: #2563eb;
        }

        .error {
            background: #fee2e2;
            color: #991b1b;
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 13px;
        }

        .top {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .back {
            font-size: 13px;
            text-decoration: none;
            color: #64748b;
        }
    </style>
</head>

<body>

<div class="card">

    <div class="top">
        <!-- 🔥 FIXED -->
        <a href="/admin/products" class="back">← Back</a>
    </div>

    <h2>✏️ Edit Product</h2>

    <!-- ERROR DISPLAY -->
    @if($errors->any())
        <div class="error">
            <ul style="margin:0; padding-left:15px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- 🔥 FIXED FORM ACTION -->
    <form method="POST" action="/admin/products/{{ $product->id }}">
        @csrf
        @method('PUT')

        <label>SKU (Product Code)</label>
        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" required>

        <label>Category</label>
        <input type="text" name="category" value="{{ old('category', $product->category) }}" required>

        <label>Product Name</label>
        <input type="text" name="name" value="{{ old('name', $product->name) }}" required>

        <label>Size</label>
        <input type="text" name="size" value="{{ old('size', $product->size) }}">

        <label>Color</label>
        <input type="text" name="color" value="{{ old('color', $product->color) }}">

        <label>Price</label>
        <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" required>

        <label>Stock</label>
        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" required>

        <label>Low Stock Threshold</label>
        <input type="number" name="low_stock_threshold"
            value="{{ old('low_stock_threshold', $product->low_stock_threshold ?? 10) }}">

        <button type="submit" class="btn">Update Product</button>
    </form>

</div>

</body>
</html>