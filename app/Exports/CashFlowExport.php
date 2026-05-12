# Simplified Cash Flow Excel Export Format

Palitan mo nalang laman ng:

```text
app/Exports/CashFlowExport.php
```

ng buong code na ito.

```php
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
                |------------------------------------------------------------------
                | DEFAULT VALUES
                |------------------------------------------------------------------
                */

                $cashIn = 0;
                $cashOut = 0;
                $totalCash = 0;

                foreach ($this->rows->toArray() as $row) {

                    $description = strtolower(trim($row['Description']));
                    $amount = (float) $row['Amount'];

                    if (
                        str_contains($description, 'deposit') ||
                        str_contains($description, 'incoming') ||
                        str_contains($description, 'a/r')
                    ) {
                        $cashIn += $amount;
                    }

                    if (
                        str_contains($description, 'expense') ||
                        str_contains($description, 'transfer')
                    ) {
                        $cashOut += $amount;
                    }

                    if (str_contains($description, 'net cash')) {
                        $totalCash = $amount;
                    }
                }

                /*
                |------------------------------------------------------------------
                | TITLE
                |------------------------------------------------------------------
                */

                $sheet->mergeCells('A1:D1');

                $sheet->setCellValue(
                    'A1',
                    'BRANCH CASH FLOW REPORT'
                );

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 20,
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                /*
                |------------------------------------------------------------------
                | DETAILS
                |------------------------------------------------------------------
                */

                $sheet->setCellValue('A3', 'Branch');
                $sheet->setCellValue('B3', $this->branch);

                $sheet->setCellValue('C3', 'Date');
                $sheet->setCellValue('D3', $this->date);

                $sheet->getStyle('A3:D3')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                /*
                |------------------------------------------------------------------
                | TOTAL CASH
                |------------------------------------------------------------------
                */

                $sheet->mergeCells('A6:D8');

                $sheet->setCellValue(
                    'A6',
                    'TOTAL CASH'
                );

                $sheet->setCellValue(
                    'A7',
                    '₱' . number_format($totalCash, 2)
                );

                $sheet->getStyle('A6:D8')->applyFromArray([
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                $sheet->getStyle('A6')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 12,
                    ],
                ]);

                $sheet->getStyle('A7')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 28,
                        'color' => ['rgb' => '16A34A'],
                    ],
                ]);

                /*
                |------------------------------------------------------------------
                | CASH SUMMARY
                |------------------------------------------------------------------
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

                $sheet->setCellValue('A12', 'Total Cash In');
                $sheet->setCellValue('B12', $cashIn);

                $sheet->setCellValue('A13', 'Total Cash Out');
                $sheet->setCellValue('B13', $cashOut);

                $sheet->setCellValue('A14', 'Net Cash');
                $sheet->setCellValue('B14', $totalCash);

                $sheet->getStyle('A14:B14')->applyFromArray([
                    'font' => [
                        'bold' => true,
                    ],
                ]);

                /*
                |------------------------------------------------------------------
                | NUMBER FORMAT
                |------------------------------------------------------------------
                */

                $sheet->getStyle('B12:B14')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                /*
                |------------------------------------------------------------------
                | BORDERS
                |------------------------------------------------------------------
                */

                $sheet->getStyle('A11:B14')->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => 'thin',
                        ],
                    ],
                ]);
            },
        ];
    }
}


