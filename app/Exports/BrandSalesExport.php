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
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class BrandSalesExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize, WithEvents
{
    protected $start, $end, $branchId, $branchName;

    public function __construct($start, $end, $branchId)
    {
        $this->start = $start;
        $this->end = $end;
        $this->branchId = $branchId;

        if ($branchId) {
            $branch = DB::table('branches')->where('id', $branchId)->first();
            $this->branchName = $branch->name ?? 'Selected Branch';
        } else {
            $this->branchName = 'All Branches';
        }
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

    public function headings(): array
    {
        return [
            '#',
            'Brand',
            'Total Sales (₱)'
        ];
    }

    public function map($row): array
    {
        static $index = 0;
        $index++;

        return [
            $index,
            $row->brand,
            $row->total
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            7 => ['font' => ['bold' => true]], // header row
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // 🔥 INSERT HEADER SPACE
                $sheet->insertNewRowBefore(1, 6);

                // 🔥 COMPANY NAME
                $sheet->setCellValue('A1', 'NICOLE TILE CENTER');
                $sheet->mergeCells('A1:C1');

                // 🔥 REPORT TITLE
                $sheet->setCellValue('A2', 'SALES PER BRAND REPORT');
                $sheet->mergeCells('A2:C2');

                // 🔥 BRANCH
                $sheet->setCellValue('A3', 'Branch: ' . $this->branchName);
                $sheet->mergeCells('A3:C3');

                // 🔥 DATE
                $dateRange = ($this->start && $this->end)
                    ? $this->start . ' - ' . $this->end
                    : 'All Dates';

                $sheet->setCellValue('A4', 'Date: ' . $dateRange);
                $sheet->mergeCells('A4:C4');

                // 🔥 GENERATED
                $sheet->setCellValue('A5', 'Generated: ' . now()->format('Y-m-d H:i:s'));
                $sheet->mergeCells('A5:C5');

                // 🔥 STYLE HEADER
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);

                $sheet->getStyle('A1:C2')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // 🔥 ERP COLOR HEADER (TABLE)
                $sheet->getStyle('A7:C7')->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '1E293B'] // dark blue-gray ERP style
                    ]
                ]);

                $startRow = 7;
                $lastRow = $sheet->getHighestRow();

                // 🔥 BORDERS
                $sheet->getStyle("A{$startRow}:C{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // 🔥 CURRENCY FORMAT
                $sheet->getStyle("C8:C{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                // 🔥 TOTAL ROW
                $totalRow = $lastRow + 1;

                $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                $sheet->setCellValue("C{$totalRow}", "=SUM(C8:C{$lastRow})");

                $sheet->getStyle("A{$totalRow}:C{$totalRow}")
                    ->getFont()
                    ->setBold(true);

                // 🔥 TOTAL BG COLOR
                $sheet->getStyle("A{$totalRow}:C{$totalRow}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('E2E8F0');
            }
        ];
    }
}