<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BrandSalesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
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

    // 🔥 HEADERS
    public function headings(): array
    {
        return [
            '#',
            'Brand',
            'Total Sales (₱)'
        ];
    }

    // 🔥 DATA
    public function map($row): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $row->brand,
            $row->total // ❗ number format sa excel na gagawin (not string)
        ];
    }

    // 🔥 STYLE HEADER ROW
    public function styles(Worksheet $sheet)
    {
        return [
            4 => ['font' => ['bold' => true]],
        ];
    }

    // 🔥 EVENTS (HEADER + TOTAL + DESIGN)
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // 🔥 TITLE
                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue('A1', 'SALES PER BRAND REPORT');
                $sheet->mergeCells('A1:C1');

                $sheet->setCellValue('A2', 'Generated: ' . now()->format('Y-m-d H:i:s'));
                $sheet->mergeCells('A2:C2');

                // 🔥 STYLE TITLE
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1:C1')->getAlignment()->setHorizontal('center');

                // 🔥 BORDERS
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A4:C{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // 🔥 PESO FORMAT
                $sheet->getStyle("C5:C{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                // 🔥 TOTAL ROW
                $totalRow = $lastRow + 1;

                $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                $sheet->setCellValue("C{$totalRow}", "=SUM(C5:C{$lastRow})");

                $sheet->getStyle("A{$totalRow}:C{$totalRow}")
                    ->getFont()
                    ->setBold(true);
            }
        ];
    }
}