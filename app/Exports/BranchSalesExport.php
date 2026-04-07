<?php

namespace App\Exports;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithEvents
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class BranchSalesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $start;
    protected $end;
    protected $branchId; // 🔥 NEW

    // 🔥 UPDATED CONSTRUCTOR
    public function __construct($start = null, $end = null, $branchId = null)
    {
        $this->start = $start;
        $this->end = $end;
        $this->branchId = $branchId;
    }

    public function collection()
    {
        return Sale::join('branches', 'sales.branch_id', '=', 'branches.id')
            ->select(
                'branches.name as Branch',
                DB::raw('SUM(sales.total_amount) as Total_Sales'),
                DB::raw('COUNT(*) as Transactions')
            )

            // 🔥 DATE FILTER
            ->when($this->start && $this->end, function ($query) {
                $query->whereBetween('sales.created_at', [$this->start, $this->end]);
            })

            // 🔥 BRANCH FILTER (ETO KULANG MO)
            ->when($this->branchId, function ($query) {
                $query->where('sales.branch_id', $this->branchId);
            })

            ->groupBy('branches.name')
            ->orderByDesc('Total_Sales')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Branch',
            'Total Sales (₱)',
            'Transactions'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            4 => ['font' => ['bold' => true]], // 🔥 FIX (header row moved down)
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // 🔥 TITLE
                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue('A1', 'SALES PER BRANCH REPORT');
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
                $sheet->getStyle("B5:B{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                // 🔥 TOTAL ROW
                $totalRow = $lastRow + 1;

                $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                $sheet->setCellValue("B{$totalRow}", "=SUM(B5:B{$lastRow})");

                $sheet->getStyle("A{$totalRow}:C{$totalRow}")
                    ->getFont()
                    ->setBold(true);
            }
        ];
    }
}