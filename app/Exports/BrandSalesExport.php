<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BrandSalesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $start, $end, $branchId;

    public function __construct($start, $end, $branchId)
    {
        $this->start = $start;
        $this->end = $end;
        $this->branchId = $branchId;
    }

    public function collection()
    {
        $query = DB::table('sales')
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->select(
                'products.name as brand',
                DB::raw('SUM(sale_items.quantity * sale_items.price) as total')
            );

        // ✅ FIX: apply date filter ONLY if both exist
        if ($this->start && $this->end) {
            $query->whereBetween('sales.created_at', [$this->start, $this->end]);
        }

        if ($this->branchId) {
            $query->where('sales.branch_id', $this->branchId);
        }

        return $query
            ->groupBy('products.name')
            ->orderByDesc('total')
            ->get();
    }

    // 🔥 COLUMN HEADERS
    public function headings(): array
    {
        return [
            '#',
            'Brand',
            'Total Sales (₱)'
        ];
    }

    // 🔥 FORMAT DATA
    public function map($row): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $row->brand,
            number_format($row->total, 2)
        ];
    }
}