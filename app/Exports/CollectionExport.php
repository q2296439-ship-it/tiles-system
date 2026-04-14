<?php

namespace App\Exports;

use App\Models\Collection;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithEvents
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CollectionExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $date;
    protected $branchId;

    public function __construct($date = null, $branchId = null)
    {
        $this->date = $date;
        $this->branchId = $branchId;
    }

    public function collection()
    {
        $rows = [];

        $collections = Collection::with(['user', 'items'])
            ->when($this->date, function ($query) {
                $query->whereDate('created_at', $this->date);
            })
            ->when($this->branchId, function ($query) {
                $query->where('branch_id', $this->branchId);
            })
            ->latest()
            ->get();

        foreach ($collections as $row) {

            $products = [];
            $qtys = [];

            foreach ($row->items as $item) {
                $products[] = $item->description;
                $qtys[] = $item->qty;
            }

            $rows[] = [
                'Receipt No' => $row->receipt_no,
                'Date'       => $row->receipt_date,
                'Customer'   => $row->customer_name,
                'Products'   => implode(', ', $products),
                'Qty'        => implode(', ', $qtys),
                'Total'      => $row->total_amount,
                'Cashier'    => $row->user->name ?? 'Cashier',
                'Time'       => $row->created_at->format('h:i A'),
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [
            'Receipt No',
            'Date',
            'Customer',
            'Products',
            'Qty',
            'Total Amount (₱)',
            'Cashier',
            'Time'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            5 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // TITLE
                $sheet->insertNewRowBefore(1, 4);

                $branchName = auth()->user()->branch->name ?? 'Current Branch';

                $sheet->setCellValue('A1', 'COLLECTION REPORT');
                $sheet->mergeCells('A1:H1');

                $sheet->setCellValue('A2', 'Generated: ' . now()->format('Y-m-d H:i:s'));
                $sheet->mergeCells('A2:H2');

                $sheet->setCellValue('A3', 'Branch: ' . $branchName);
                $sheet->mergeCells('A3:H3');

                $sheet->setCellValue('A4', 'Date: ' . ($this->date ?? 'All'));
                $sheet->mergeCells('A4:H4');

                // STYLE TITLE
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal('center');

                // BORDERS
                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A5:H{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(
                        \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    );

                // MONEY FORMAT
                $sheet->getStyle("F6:F{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                // TOTAL ROW
                $totalRow = $lastRow + 1;

                $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                $sheet->setCellValue("F{$totalRow}", "=SUM(F6:F{$lastRow})");

                $sheet->getStyle("A{$totalRow}:H{$totalRow}")
                    ->getFont()
                    ->setBold(true);
            }
        ];
    }
}