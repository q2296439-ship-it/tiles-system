<?php

namespace App\Exports;

use App\Models\StockMovement;
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

class TransferOutExport implements FromCollection, WithHeadings, WithStyles, ShouldAutoSize, WithEvents
{
    protected $search;
    protected $status;

    public function __construct($search = null, $status = null)
    {
        $this->search = $search;
        $this->status = $status;
    }

    public function collection()
    {
        $query = StockMovement::with([
            'product',
            'requester',
            'approver'
        ])->where('type', 'OUT');

        if ($this->search) {
            $query->whereHas('product', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $query->latest()->get()->map(function ($t) {
            return [
                'Product'      => $t->product->name ?? '-',
                'From Branch'  => $t->from_branch_name ?? '-',
                'To Branch'    => $t->to_branch_name ?? '-',
                'Quantity'     => $t->quantity,
                'Status'       => ucfirst($t->status),
                'Requested By' => $t->requester->name ?? '-',
                'Approved By'  => $t->approver->name ?? '-',
                'Date'         => optional($t->created_at)->format('Y-m-d'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Product',
            'From Branch',
            'To Branch',
            'Quantity',
            'Status',
            'Requested By',
            'Approved By',
            'Date'
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
            AfterSheet::class => function ($event) {

                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue('A1', 'TRANSFER OUT REPORT');
                $sheet->mergeCells('A1:H1');

                $sheet->setCellValue('A2', 'Generated: ' . now()->format('Y-m-d H:i:s'));
                $sheet->mergeCells('A2:H2');

                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1:H1')->getAlignment()->setHorizontal('center');

                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A4:H{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $totalRow = $lastRow + 1;

                $sheet->setCellValue("C{$totalRow}", 'TOTAL QTY');
                $sheet->setCellValue("D{$totalRow}", "=SUM(D5:D{$lastRow})");

                $sheet->getStyle("C{$totalRow}:D{$totalRow}")
                    ->getFont()
                    ->setBold(true);
            }
        ];
    }
}