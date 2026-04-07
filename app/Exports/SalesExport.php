<?php

namespace App\Exports;

use App\Models\Sale;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithEvents
};
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class SalesExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $start;
    protected $end;
    protected $total = 0;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end = $end;
    }

    public function collection()
    {
        $sales = Sale::with(['branch','user','items.product'])
            ->whereBetween('created_at', [$this->start, $this->end])
            ->get();

        $rows = collect();

        foreach ($sales as $sale) {

            foreach ($sale->items as $item) {

                $rows->push([
                    'Date & Time' => Carbon::parse($sale->created_at)->format('M d, Y h:i A'),
                    'Branch' => $sale->branch->name ?? 'N/A',
                    'Cashier' => $sale->user->name ?? 'N/A',
                    'Product' => $item->product->name ?? 'N/A',
                    'Qty' => $item->quantity,
                    'Amount' => $item->quantity * $item->price
                ]);
            }

        }

        $this->total = $rows->sum('Amount');

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Date & Time',
            'Branch',
            'Cashier',
            'Product',
            'Qty',
            'Amount'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            4 => [
                'font' => ['bold' => true],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {

                $sheet = $event->sheet;

                // 🔥 INSERT HEADER SPACE
                $sheet->insertNewRowBefore(1, 3);

                // 🔥 COMPANY NAME
                $sheet->setCellValue('A1', 'NICOLE TILES CENTER');
                $sheet->mergeCells('A1:F1');

                // 🔥 REPORT TITLE
                $sheet->setCellValue('A2', 'Sales Report');
                $sheet->mergeCells('A2:F2');

                // 🔥 DATE RANGE
                $sheet->setCellValue('A3',
                    date('F d, Y', strtotime($this->start)) .
                    ' - ' .
                    date('F d, Y', strtotime($this->end))
                );
                $sheet->mergeCells('A3:F3');

                // 🎨 STYLE TITLE
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 16],
                    'alignment' => ['horizontal' => 'center']
                ]);

                $sheet->getStyle('A2')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 12],
                    'alignment' => ['horizontal' => 'center']
                ]);

                $sheet->getStyle('A3')->applyFromArray([
                    'alignment' => ['horizontal' => 'center']
                ]);

                // 🔥 HEADER STYLE (ROW 4)
                $sheet->getStyle('A4:F4')->applyFromArray([
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '111827'],
                    ],
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                ]);

                $sheet->getStyle('A4:F4')->getAlignment()->setHorizontal('center');

                // 🔥 LAST ROW
                $lastRow = $sheet->getHighestRow() + 1;

                // 🔥 TOTAL
                $sheet->setCellValue('E' . $lastRow, 'TOTAL:');
                $sheet->setCellValue('F' . $lastRow, $this->total);

                $sheet->getStyle('E' . $lastRow . ':F' . $lastRow)
                    ->getFont()->setBold(true);

                // 🔥 AMOUNT FORMAT
                $sheet->getStyle('F5:F' . $lastRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle('F5:F' . $lastRow)
                    ->getAlignment()
                    ->setHorizontal('right');
            }
        ];
    }
}