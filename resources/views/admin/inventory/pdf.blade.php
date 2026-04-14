<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Inventory Report</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
        }

        p {
            text-align: center;
            margin-top: 0;
            margin-bottom: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 6px;
            font-size: 11px;
        }

        th {
            background: #f2f2f2;
        }

        .right {
            text-align: right;
        }
    </style>
</head>
<body>

    <h2>Inventory Report</h2>
    <p>Date: {{ now()->format('F d, Y h:i A') }}</p>

    <table>
        <thead>
            <tr>
                <th>Product</th>
                <th>Branch</th>
                <th>Size</th>
                <th>Color</th>
                <th>Price</th>
                <th>Stock</th>
                <th>Total Value</th>
            </tr>
        </thead>

        <tbody>
        @foreach($products as $p)
            <tr>
                <td>{{ $p->name }}</td>
                <td>{{ $p->branch->name ?? '-' }}</td>
                <td>{{ $p->size }}</td>
                <td>{{ $p->color }}</td>
                <td class="right">₱{{ number_format($p->price, 2) }}</td>
                <td class="right">{{ $p->stock }}</td>
                <td class="right">₱{{ number_format($p->price * $p->stock, 2) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

</body>
</html>