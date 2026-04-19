<?php

namespace App\Exports;

use App\Models\Collection;
use App\Models\ReturnModel;
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

class CollectionExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $date;
    protected $branchId;
    protected $status;

    public function __construct($date = null, $branchId = null, $status = 'all')
    {
        $this->date = $date;
        $this->branchId = $branchId;
        $this->status = $status;
    }

    public function collection()
    {
        $rows = [];

        $collections = Collection::with(['user', 'items'])
            ->when($this->date, function ($query) {
                $query->whereDate('receipt_date', $this->date);
            })
            ->when($this->branchId, function ($query) {
                $query->where('branch_id', $this->branchId);
            })
            ->get()
            ->map(function ($row) {
                $row->record_type = strtolower($row->status ?? 'saved');
                return $row;
            });

        $returns = ReturnModel::with(['user', 'items'])
            ->when($this->date, function ($query) {
                $query->whereDate('return_date', $this->date);
            })
            ->when($this->branchId, function ($query) {
                $query->where('branch_id', $this->branchId);
            })
            ->get()
            ->map(function ($row) {
                $row->receipt_date = $row->return_date;
                $row->record_type = 'returned';
                return $row;
            });

        $records = $collections->concat($returns);

        if ($this->status != 'all') {
            $records = $records->where('record_type', $this->status);
        }

        $records = $records->sortByDesc('created_at')->values();

        foreach ($records as $row) {

            $products = [];
            $qtys = [];

            foreach ($row->items as $item) {
                $products[] = $item->description;
                $qtys[] = $item->qty;
            }

            $rows[] = [
                'Receipt No'   => $row->receipt_no,
                'Date'         => $row->receipt_date,
                'Customer'     => $row->customer_name,
                'Products'     => implode(', ', $products),
                'Qty'          => implode(', ', $qtys),
                'Total Sales'  => $row->total_amount,
                'Paid Amount'  => $row->paid_amount ?? 0,
                'Balance'      => $row->balance ?? 0,
                'Cashier'      => $row->user->name ?? 'Cashier',
                'Time'         => $row->created_at->format('h:i A'),
                'Status'       => ucfirst($row->record_type),
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
            'Total Sales (₱)',
            'Paid Amount (₱)',
            'Balance (₱)',
            'Cashier',
            'Time',
            'Status'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            6 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 5);

                $branchName = 'Current Branch';

                if (auth()->check()) {
                    $branchName = optional(auth()->user()->branch)->name ?? 'Current Branch';
                }

                $sheet->setCellValue('A1', 'COLLECTION REPORT');
                $sheet->mergeCells('A1:K1');

                $sheet->setCellValue('A2', 'Generated: ' . now()->format('Y-m-d H:i:s'));
                $sheet->mergeCells('A2:K2');

                $sheet->setCellValue('A3', 'Branch: ' . $branchName);
                $sheet->mergeCells('A3:K3');

                $sheet->setCellValue(
                    'A4',
                    'Date: ' . ($this->date ?? 'All') .
                    ' | Status: ' . ucfirst($this->status)
                );
                $sheet->mergeCells('A4:K4');

                $lastRow = $sheet->getHighestRow();

                $sheet->setCellValue('A5', 'Total Receipts: ' . ($lastRow - 6));
                $sheet->mergeCells('A5:C5');

                $sheet->setCellValue('D5', '=SUM(F7:F' . $lastRow . ')');
                $sheet->mergeCells('D5:F5');

                $sheet->setCellValue('G5', '=SUM(G7:G' . $lastRow . ')');
                $sheet->mergeCells('G5:I5');

                $sheet->setCellValue('J5', 'Summary');
                $sheet->mergeCells('J5:K5');

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1:K1')->getAlignment()->setHorizontal('center');

                $sheet->getStyle("A6:K{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle("F7:H{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $totalRow = $lastRow + 1;

                $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                $sheet->setCellValue("F{$totalRow}", "=SUM(F7:F{$lastRow})");
                $sheet->setCellValue("G{$totalRow}", "=SUM(G7:G{$lastRow})");
                $sheet->setCellValue("H{$totalRow}", "=SUM(H7:H{$lastRow})");

                $sheet->getStyle("A{$totalRow}:K{$totalRow}")
                    ->getFont()
                    ->setBold(true);
            }
        ];
    }
}