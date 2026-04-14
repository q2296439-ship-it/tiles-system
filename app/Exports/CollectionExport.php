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

        // collections (saved/cancelled)
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

        // returns
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
                'Receipt No' => $row->receipt_no,
                'Date'       => $row->receipt_date,
                'Customer'   => $row->customer_name,
                'Products'   => implode(', ', $products),
                'Qty'        => implode(', ', $qtys),
                'Total'      => $row->total_amount,
                'Cashier'    => $row->user->name ?? 'Cashier',
                'Time'       => $row->created_at->format('h:i A'),
                'Status'     => ucfirst($row->record_type),
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
            'Time',
            'Status'
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

                $sheet->insertNewRowBefore(1, 4);

                $branchName = auth()->user()->branch->name ?? 'Current Branch';

                $sheet->setCellValue('A1', 'COLLECTION REPORT');
                $sheet->mergeCells('A1:I1');

                $sheet->setCellValue('A2', 'Generated: ' . now()->format('Y-m-d H:i:s'));
                $sheet->mergeCells('A2:I2');

                $sheet->setCellValue('A3', 'Branch: ' . $branchName);
                $sheet->mergeCells('A3:I3');

                $sheet->setCellValue(
                    'A4',
                    'Date: ' . ($this->date ?? 'All') .
                    ' | Status: ' . ucfirst($this->status)
                );
                $sheet->mergeCells('A4:I4');

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1:I1')->getAlignment()->setHorizontal('center');

                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A5:I{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(
                        \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
                    );

                $sheet->getStyle("F6:F{$lastRow}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $totalRow = $lastRow + 1;

                $sheet->setCellValue("A{$totalRow}", 'TOTAL');
                $sheet->setCellValue("F{$totalRow}", "=SUM(F6:F{$lastRow})");

                $sheet->getStyle("A{$totalRow}:I{$totalRow}")
                    ->getFont()
                    ->setBold(true);
            }
        ];
    }
}