<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class CashFlowExport implements FromArray, ShouldAutoSize, WithEvents
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

    public function array(): array
    {
        $data = [];

        // TITLE
        $data[] = ['BRANCH CASH FLOW STATEMENT'];
        $data[] = [''];

        // DETAILS
        $data[] = ['Branch', $this->branch ?? 'All Branches'];
        $data[] = ['Date', $this->date];
        $data[] = [''];

        // HEADERS
        $data[] = ['Description', 'Amount'];

        // ROWS
        foreach ($this->rows as $row) {
            $data[] = [
                $row['Description'],
                $row['Amount']
            ];
        }

        return $data;
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet;

                // TITLE
                $sheet->mergeCells('A1:B1');

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 18,
                    ],
                    'alignment' => [
                        'horizontal' => 'center',
                    ],
                ]);

                // HEADER STYLE
                $sheet->getStyle('A6:B6')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => 'solid',
                        'startColor' => ['rgb' => '1D4ED8'],
                    ],
                ]);

                // BOLD NET CASH
                $highestRow = $sheet->getHighestRow();

                $sheet->getStyle('A' . $highestRow . ':B' . $highestRow)
                    ->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                        ],
                    ]);

                // MONEY FORMAT
                $sheet->getStyle('B7:B' . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                // BORDERS
                $sheet->getStyle('A6:B' . $highestRow)
                    ->applyFromArray([
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