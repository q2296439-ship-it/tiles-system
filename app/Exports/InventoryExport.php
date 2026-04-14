<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithEvents
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class InventoryExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $search;
    protected $branchId;

    public function __construct($search = null, $branchId = null)
    {
        $this->search = $search;
        $this->branchId = $branchId;
    }

    public function collection()
    {
        $query = Product::with('branch');

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        if ($this->branchId) {
            $query->where('branch_id', $this->branchId);
        }

        return $query->get()->map(function ($p) {
            return [
                'Product'     => $p->name,
                'Branch'      => optional($p->branch)->name,
                'Size'        => $p->size,
                'Color'       => $p->color,
                'Price'       => $p->price,
                'Stock'       => $p->stock,
                'Total Value' => $p->price * $p->stock,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Product',
            'Branch',
            'Size',
            'Color',
            'Price',
            'Stock',
            'Total Value'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            4 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // TITLE
                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue('A1', 'INVENTORY STOCK REPORT');
                $sheet->mergeCells('A1:G1');

                $sheet->setCellValue('A2', 'Generated: ' . now()->format('Y-m-d H:i:s'));
                $sheet->mergeCells('A2:G2');

                // TITLE STYLE
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1:G1')->getAlignment()->setHorizontal('center');

                // BORDERS
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A4:G{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // MONEY FORMAT
                $sheet->getStyle("E5:E{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle("G5:G{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                // TOTAL ROW
                $totalRow = $lastRow + 1;

                $sheet->setCellValue("F{$totalRow}", 'TOTAL');
                $sheet->setCellValue("G{$totalRow}", "=SUM(G5:G{$lastRow})");

                $sheet->getStyle("F{$totalRow}:G{$totalRow}")
                    ->getFont()
                    ->setBold(true);
            }
        ];
    }
}