<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

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
                | PAGE SETUP
                |--------------------------------------------------------------------------
                */

                $sheet->getPageSetup()
                    ->setOrientation(
                        \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT
                    );

                $sheet->getPageSetup()
                    ->setPaperSize(
                        \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
                    );

                /*
                |--------------------------------------------------------------------------
                | WIDTHS
                |--------------------------------------------------------------------------
                */

                $sheet->getColumnDimension('A')->setWidth(40);
                $sheet->getColumnDimension('B')->setWidth(25);

                /*
                |--------------------------------------------------------------------------
                | TITLE
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A1:B1');

                $sheet->setCellValue(
                    'A1',
                    'BRANCH CASH FLOW STATEMENT'
                );

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                        'color' => ['rgb' => '0F172A'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | SUBTITLE
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A2:B2');

                $sheet->setCellValue(
                    'A2',
                    'Financial Position Report'
                );

                $sheet->getStyle('A2')->applyFromArray([
                    'font' => [
                        'italic' => true,
                        'size' => 10,
                        'color' => ['rgb' => '64748B'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | DETAILS
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue('A4', 'Branch');
                $sheet->setCellValue(
                    'B4',
                    $this->branch ?? 'All Branches'
                );

                $sheet->setCellValue('A5', 'Date');
                $sheet->setCellValue(
                    'B5',
                    $this->date
                );

                $sheet->getStyle('A4:A5')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | AVAILABLE CASH
                |--------------------------------------------------------------------------
                */

                $netCash = 0;

                foreach ($this->rows as $row) {

                    if ($row['Description'] === 'NET CASH') {
                        $netCash = $row['Amount'];
                    }
                }

                $sheet->mergeCells('A7:B7');

                $sheet->setCellValue(
                    'A7',
                    'AVAILABLE CASH'
                );

                $sheet->mergeCells('A8:B8');

                $sheet->setCellValue(
                    'A8',
                    '₱' . number_format($netCash, 2)
                );

                $sheet->getStyle('A7')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => '64748B'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                $sheet->getStyle('A8')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 24,
                        'color' => ['rgb' => '16A34A'],
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | SUMMARY TABLE
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue('A11', 'Description');
                $sheet->setCellValue('B11', 'Amount');

                $sheet->getStyle('A11:B11')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '2563EB'],
                    ],
                ]);

                $rowNumber = 12;

                foreach ($this->rows as $row) {

                    $sheet->setCellValue(
                        'A' . $rowNumber,
                        $row['Description']
                    );

                    $sheet->setCellValue(
                        'B' . $rowNumber,
                        $row['Amount']
                    );

                    $sheet->getStyle('B' . $rowNumber)
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');

                    /*
                    |--------------------------------------------------------------------------
                    | COLORS
                    |--------------------------------------------------------------------------
                    */

                    if (
                        str_contains(
                            strtolower($row['Description']),
                            'expense'
                        )
                    ) {

                        $sheet->getStyle('B' . $rowNumber)
                            ->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'color' => ['rgb' => 'DC2626'],
                                ],
                            ]);
                    }

                    if (
                        str_contains(
                            strtolower($row['Description']),
                            'deposit'
                        )
                    ) {

                        $sheet->getStyle('B' . $rowNumber)
                            ->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'color' => ['rgb' => '16A34A'],
                                ],
                            ]);
                    }

                    if ($row['Description'] === 'NET CASH') {

                        $sheet->getStyle(
                            'A' . $rowNumber . ':B' . $rowNumber
                        )->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 12,
                            ],
                            'fill' => [
                                'fillType' => 'solid',
                                'startColor' => ['rgb' => 'DBEAFE'],
                            ],
                        ]);
                    }

                    $rowNumber++;
                }

                /*
                |--------------------------------------------------------------------------
                | BORDERS
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle(
                    'A11:B' . ($rowNumber - 1)
                )->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => 'CBD5E1'],
                        ],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | ALIGNMENT
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle(
                    'B11:B' . ($rowNumber - 1)
                )->applyFromArray([
                    'alignment' => [
                        'horizontal' => 'right',
                    ],
                ]);
            },
        ];
    }
}