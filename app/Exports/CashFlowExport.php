<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;

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
                $sheet->getColumnDimension('C')->setWidth(10);
                $sheet->getColumnDimension('D')->setWidth(40);
                $sheet->getColumnDimension('E')->setWidth(25);

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
                        'size' => 20,
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

                $sheet->mergeCells('A2:E2');

                $sheet->setCellValue(
                    'A2',
                    'Financial Position Report • Real-Time Business Monitoring'
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

                $sheet->setCellValue('D4', 'Date');
                $sheet->setCellValue(
                    'E4',
                    $this->date
                );

                $sheet->getStyle('A4:E4')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | DEFAULT VALUES
                |--------------------------------------------------------------------------
                */

                $actualDeposit = 0;
                $arPayments = 0;
                $incomingTransfers = 0;
                $expenses = 0;
                $outgoingTransfers = 0;
                $netCash = 0;

                /*
                |--------------------------------------------------------------------------
                | GET VALUES FROM ROWS
                |--------------------------------------------------------------------------
                */

                foreach ($this->rows->toArray() as $row) {

                    $description = strtolower(trim($row['Description']));
                    $amount = (float) $row['Amount'];

                    if (str_contains($description, 'actual deposit')) {
                        $actualDeposit = $amount;
                    }

                    if (
                        str_contains($description, 'a/r payment')
                        || str_contains($description, 'a/r payments')
                    ) {
                        $arPayments = $amount;
                    }

                    if (str_contains($description, 'incoming')) {
                        $incomingTransfers = $amount;
                    }

                    if (str_contains($description, 'expense')) {
                        $expenses = $amount;
                    }

                    if (
                        str_contains($description, 'outgoing')
                        || str_contains($description, 'transfer to other branch')
                    ) {
                        $outgoingTransfers = $amount;
                    }

                    if (str_contains($description, 'net cash')) {
                        $netCash = $amount;
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | COMPUTE TOTALS
                |--------------------------------------------------------------------------
                */

                $todayCashIn =
                    $actualDeposit +
                    $arPayments +
                    $incomingTransfers;

                $todayCashOut =
                    $expenses +
                    $outgoingTransfers;

                $previousBalance =
                    $netCash -
                    ($todayCashIn - $todayCashOut);

                /*
                |--------------------------------------------------------------------------
                | AVAILABLE CASH
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A7:E9');

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

                $sheet->getStyle('A7:E9')->applyFromArray([
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                $sheet->getStyle('A7')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 11,
                        'color' => ['rgb' => '64748B'],
                    ],
                ]);

                $sheet->getStyle('A8')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 28,
                        'color' => ['rgb' => '16A34A'],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | MAIN SUMMARY
                |--------------------------------------------------------------------------
                */

                $sheet->setCellValue('A12', 'Beginning Balance');
                $sheet->setCellValue('E12', $previousBalance);

                $sheet->setCellValue('A13', 'Today Cash In');
                $sheet->setCellValue('E13', $todayCashIn);

                $sheet->setCellValue('A14', 'Today Cash Out');
                $sheet->setCellValue('E14', $todayCashOut);

                $sheet->setCellValue('A16', 'Running Balance');
                $sheet->setCellValue('E16', $netCash);

                $sheet->getStyle('A12:E16')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                $sheet->getStyle('E12')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '2563EB'],
                    ],
                ]);

                $sheet->getStyle('E13')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => '16A34A'],
                    ],
                ]);

                $sheet->getStyle('E14')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'DC2626'],
                    ],
                ]);

                $sheet->getStyle('E16')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => '2563EB'],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | CASH IN BOX
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A19:B19');

                $sheet->setCellValue(
                    'A19',
                    '💰 CASH IN'
                );

                $sheet->setCellValue('A21', 'Actual Deposit Amount');
                $sheet->setCellValue('B21', $actualDeposit);

                $sheet->setCellValue('A22', 'A/R Payment');
                $sheet->setCellValue('B22', $arPayments);

                $sheet->setCellValue('A23', 'Incoming Transfers');
                $sheet->setCellValue('B23', $incomingTransfers);

                $sheet->setCellValue('A25', 'Total Cash In');
                $sheet->setCellValue('B25', $todayCashIn);

                $sheet->getStyle('A19:B25')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '1E3A8A'],
                        ],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | CASH OUT BOX
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('D19:E19');

                $sheet->setCellValue(
                    'D19',
                    '💸 CASH OUT'
                );

                $sheet->setCellValue('D21', 'Expenses');
                $sheet->setCellValue('E21', $expenses);

                $sheet->setCellValue('D22', 'Transfer to Other Branch');
                $sheet->setCellValue('E22', $outgoingTransfers);

                $sheet->setCellValue('D24', 'Total Cash Out');
                $sheet->setCellValue('E24', $todayCashOut);

                $sheet->getStyle('D19:E24')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '1E3A8A'],
                        ],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | NET CASH POSITION
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A28:E30');

                $sheet->setCellValue(
                    'A29',
                    '📊 NET CASH POSITION'
                );

                $sheet->setCellValue(
                    'A30',
                    'Beginning + Today In - Today Out'
                );

                $sheet->setCellValue(
                    'E30',
                    '₱' . number_format($netCash, 2)
                );

                $sheet->getStyle('A28:E30')->applyFromArray([
                    'borders' => [
                        'outline' => [
                            'borderStyle' => 'thin',
                            'color' => ['rgb' => '1E3A8A'],
                        ],
                    ],
                ]);

                /*
                |--------------------------------------------------------------------------
                | NUMBER FORMAT
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('E12:E16')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle('B21:B25')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle('E21:E24')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');
            },
        ];
    }
}