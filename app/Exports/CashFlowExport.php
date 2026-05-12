<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class CashFlowExport implements WithEvents, ShouldAutoSize
{
    protected $rows;
    protected $date;
    protected $branch;

    public function __construct(Collection $rows, $date = null, $branch = null)
    {
        $this->rows = $rows;
        $this->date = $date;
        $this->branch = $branch;
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                /*
                |--------------------------------------------------------------------------
                | WIDTHS
                |--------------------------------------------------------------------------
                */

                $sheet->getColumnDimension('A')->setWidth(28);
                $sheet->getColumnDimension('B')->setWidth(22);
                $sheet->getColumnDimension('C')->setWidth(10);
                $sheet->getColumnDimension('D')->setWidth(28);
                $sheet->getColumnDimension('E')->setWidth(22);

                /*
                |--------------------------------------------------------------------------
                | BACKGROUND
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A1:E40')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'DCEBFF'
                        ]
                    ]
                ]);

                /*
                |--------------------------------------------------------------------------
                | TITLE
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A1:E1');

                $sheet->setCellValue(
                    'A1',
                    'BRANCH CASH FLOW STATEMENT'
                );

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 22,
                        'color' => ['rgb' => '0F172A']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                /*
                |--------------------------------------------------------------------------
                | SUBTITLE
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A2:E2');

                $sheet->setCellValue(
                    'A2',
                    'Financial Position Report • Real-Time Business Monitoring'
                );

                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10,
                        'color' => ['rgb' => '64748B']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                /*
                |--------------------------------------------------------------------------
                | DATE / BRANCH
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue('A4', 'Branch');
                $sheet->setCellValue('B4', $this->branch ?? 'All Branches');

                $sheet->setCellValue('D4', 'Date');
                $sheet->setCellValue('E4', $this->date);

                $sheet->getStyle('A4:E4')->applyFromArray([
                    'font' => [
                        'bold' => true
                    ]
                ]);

                /*
                |--------------------------------------------------------------------------
                | FIND VALUES
                |--------------------------------------------------------------------------
                */

                $cashIn = 0;
                $cashOut = 0;
                $netCash = 0;

                foreach ($this->rows as $row) {

                    if ($row['Description'] === 'Actual Deposit') {
                        $cashIn += $row['Amount'];
                    }

                    if ($row['Description'] === 'A/R Payments') {
                        $cashIn += $row['Amount'];
                    }

                    if ($row['Description'] === 'Incoming Transfers') {
                        $cashIn += $row['Amount'];
                    }

                    if ($row['Description'] === 'Expenses') {
                        $cashOut += abs($row['Amount']);
                    }

                    if ($row['Description'] === 'Outgoing Transfers') {
                        $cashOut += abs($row['Amount']);
                    }

                    if ($row['Description'] === 'NET CASH') {
                        $netCash = $row['Amount'];
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | AVAILABLE CASH BOX
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A6:E10');

                $sheet->setCellValue(
                    'A7',
                    'AVAILABLE CASH'
                );

                $sheet->setCellValue(
                    'A8',
                    '₱' . number_format($netCash, 2)
                );

                $sheet->setCellValue(
                    'A9',
                    'Current usable cash balance of selected branch'
                );

                $sheet->getStyle('A6:E10')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'FFFFFF'
                        ]
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '1E3A8A']
                        ]
                    ]
                ]);

                $sheet->getStyle('A7')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                        'color' => ['rgb' => '64748B']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                $sheet->getStyle('A8')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 28,
                        'color' => ['rgb' => '16A34A']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                $sheet->getStyle('A9')->applyFromArray([
                    'font' => [
                        'size' => 10,
                        'color' => ['rgb' => '64748B']
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER
                    ]
                ]);

                /*
                |--------------------------------------------------------------------------
                | CASH IN BOX
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A12:B12');

                $sheet->setCellValue('A12', '💰 CASH IN');

                $sheet->setCellValue('A14', 'Actual Deposit');
                $sheet->setCellValue('B14', $cashIn);

                $sheet->setCellValue('A15', 'Total Cash In');
                $sheet->setCellValue('B15', $cashIn);

                $sheet->getStyle('A12:B16')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'FFFFFF'
                        ]
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '1E3A8A']
                        ]
                    ]
                ]);

                /*
                |--------------------------------------------------------------------------
                | CASH OUT BOX
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('D12:E12');

                $sheet->setCellValue('D12', '💸 CASH OUT');

                $sheet->setCellValue('D14', 'Expenses');
                $sheet->setCellValue('E14', $cashOut);

                $sheet->setCellValue('D15', 'Total Cash Out');
                $sheet->setCellValue('E15', $cashOut);

                $sheet->getStyle('D12:E16')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'FFFFFF'
                        ]
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '1E3A8A']
                        ]
                    ]
                ]);

                /*
                |--------------------------------------------------------------------------
                | NET CASH POSITION
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A19:E22');

                $sheet->setCellValue(
                    'A20',
                    '📊 NET CASH POSITION'
                );

                $sheet->setCellValue(
                    'A21',
                    'Beginning + Today In - Today Out'
                );

                $sheet->setCellValue(
                    'E21',
                    '₱' . number_format($netCash, 2)
                );

                $sheet->getStyle('A19:E22')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => [
                            'rgb' => 'FFFFFF'
                        ]
                    ],
                    'borders' => [
                        'outline' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '1E3A8A']
                        ]
                    ]
                ]);

                /*
                |--------------------------------------------------------------------------
                | COLORS
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('B14:B15')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '16A34A']
                    ]
                ]);

                $sheet->getStyle('E14:E15')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'DC2626']
                    ]
                ]);

                $sheet->getStyle('E21')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                        'color' => ['rgb' => '2563EB']
                    ]
                ]);

                /*
                |--------------------------------------------------------------------------
                | ALIGNMENT
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A1:E30')->applyFromArray([
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER
                    ]
                ]);

                /*
                |--------------------------------------------------------------------------
                | NUMBER FORMAT
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('B14:B15')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle('E14:E15')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            },
        ];
    }
}