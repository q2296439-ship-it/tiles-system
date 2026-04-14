<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nicole Tiles Center - Inventory Report</title>

    <style>
        body{
            font-family: Arial, sans-serif;
            font-size:12px;
            color:#111;
            padding:30px;
        }

        .topbar{
            height:6px;
            background:#111;
            margin-bottom:15px;
        }

        .header{
            margin-bottom:20px;
        }

        .company{
            font-size:18px;
            font-weight:bold;
        }

        .report-title{
            font-size:13px;
            color:#444;
        }

        .date{
            font-size:11px;
            color:#666;
            margin-top:5px;
            line-height:1.6;
        }

        .summary{
            margin-top:15px;
            margin-bottom:25px;
        }

        .summary table{
            width:100%;
            border-collapse:collapse;
        }

        .summary td{
            width:33.33%;
            padding:8px;
        }

        .summary-box{
            border:1px solid #000;
            padding:10px;
        }

        .label{
            font-size:11px;
            color:#555;
        }

        .value{
            font-size:14px;
            font-weight:bold;
            margin-top:5px;
        }

        table.main{
            width:100%;
            border-collapse:collapse;
        }

        table.main th{
            background:#111;
            color:#fff;
            font-size:12px;
        }

        table.main th,
        table.main td{
            border:1px solid #000;
            padding:7px;
        }

        td.right{
            text-align:right;
        }

        .low{
            background:#fff3cd;
            font-weight:bold;
        }

        .footer{
            margin-top:25px;
        }

        .grand{
            text-align:right;
            font-size:14px;
            font-weight:bold;
        }

        .signature{
            margin-top:50px;
        }

        .line{
            width:220px;
            border-top:1px solid #000;
            margin-top:40px;
        }
    </style>
</head>

<body>

<div class="topbar"></div>

<!-- HEADER -->
<div class="header">
    <div class="company">NICOLE TILES CENTER</div>
    <div class="report-title">Inventory Stock Overview Report</div>

    <div class="date">
        Generated: {{ now()->format('F d, Y h:i A') }} <br>

        @if(request('branch_id'))
            Branch Filter: {{ optional($products->first()->branch)->name ?? 'Selected Branch' }} <br>
        @else
            Branch Filter: All Branches <br>
        @endif

        @if(request('search'))
            Search: {{ request('search') }}
        @endif
    </div>
</div>

<!-- SUMMARY -->
<div class="summary">
    <table>
        <tr>
            <td>
                <div class="summary-box">
                    <div class="label">TOTAL PRODUCTS</div>
                    <div class="value">{{ $products->count() }}</div>
                </div>
            </td>

            <td>
                <div class="summary-box">
                    <div class="label">LOW STOCK ITEMS</div>
                    <div class="value">
                        {{ $products->where('stock','<=',10)->count() }}
                    </div>
                </div>
            </td>

            <td>
                <div class="summary-box">
                    <div class="label">TOTAL INVENTORY VALUE</div>
                    <div class="value">
                        PHP {{ number_format($products->sum(fn($p) => $p->price * $p->stock),2) }}
                    </div>
                </div>
            </td>
        </tr>
    </table>
</div>

<!-- TABLE -->
<table class="main">
    <thead>
        <tr>
            <th width="5%">#</th>
            <th>Product</th>
            <th>Branch</th>
            <th>Size</th>
            <th>Color</th>
            <th width="12%">Price</th>
            <th width="10%">Stock</th>
            <th width="14%">Status</th>
        </tr>
    </thead>

    <tbody>
        @foreach($products as $index => $p)
        <tr class="{{ $p->stock <= 10 ? 'low' : '' }}">
            <td>{{ $index + 1 }}</td>
            <td>{{ $p->name }}</td>
            <td>{{ $p->branch->name ?? '-' }}</td>
            <td>{{ $p->size }}</td>
            <td>{{ $p->color }}</td>
            <td class="right">PHP {{ number_format($p->price,2) }}</td>
            <td class="right">{{ $p->stock }}</td>
            <td>
                {{ $p->stock <= 10 ? 'LOW STOCK' : 'AVAILABLE' }}
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

<!-- FOOTER -->
<div class="footer">
    <div class="grand">
        TOTAL VALUE:
        PHP {{ number_format($products->sum(fn($p) => $p->price * $p->stock),2) }}
    </div>

    <div class="signature">
        <div class="line"></div>
        <small>Authorized Signature</small>
    </div>
</div>

</body>
</html>