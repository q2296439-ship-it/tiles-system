<?php

namespace App\Exports;

use App\Models\Deposit;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    ShouldAutoSize,
    WithEvents
};
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Border;

class DepositExport implements FromCollection, WithHeadings, ShouldAutoSize, WithEvents
{
    protected $date;
    protected $branchId;
    protected $role;
    protected $userBranch;

    public function __construct($date, $branchId, $role, $userBranch)
    {
        $this->date = $date;
        $this->branchId = $branchId;
        $this->role = $role;
        $this->userBranch = $userBranch;
    }

    public function collection()
    {
        $query = Deposit::leftJoin('branches', 'deposits.branch_id', '=', 'branches.id')
            ->select(
                'branches.name as branch',
                'deposits.deposit_date',
                'deposits.denom_1000',
                'deposits.denom_500',
                'deposits.denom_200',
                'deposits.denom_100',
                'deposits.denom_50',
                'deposits.denom_20',
                'deposits.coin_10',
                'deposits.coin_5',
                'deposits.coin_1',
                'deposits.actual_amount',
                'deposits.variance'
            )
            ->whereDate('deposits.deposit_date', $this->date);

        if ($this->role === 'cashier') {
            $query->where('deposits.branch_id', $this->userBranch);
        } elseif (!empty($this->branchId)) {
            $query->where('deposits.branch_id', $this->branchId);
        }

        return $query->get()->map(function ($row) {
            return [
                $row->branch,
                $row->deposit_date,
                $row->denom_1000,
                $row->denom_500,
                $row->denom_200,
                $row->denom_100,
                $row->denom_50,
                $row->denom_20,
                $row->coin_10,
                $row->coin_5,
                $row->coin_1,
                $row->actual_amount,
                $row->variance,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Branch',
            'Date',
            '1000',
            '500',
            '200',
            '100',
            '50',
            '20',
            '10',
            '5',
            '1',
            'Actual Deposit',
            'Variance'
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function($event){

                $sheet = $event->sheet->getDelegate();

                $sheet->insertNewRowBefore(1, 3);

                $sheet->setCellValue('A1', 'NICOLE TILES CENTER');
                $sheet->mergeCells('A1:M1');

                $sheet->setCellValue('A2', 'CASH DEPOSIT REPORT');
                $sheet->mergeCells('A2:M2');

                $sheet->setCellValue('A3', 'Generated: ' . now()->format('Y-m-d h:i A'));
                $sheet->mergeCells('A3:M3');

                $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
                $sheet->getStyle('A1:M3')->getAlignment()->setHorizontal('center');

                $lastRow = $sheet->getHighestRow();

                $sheet->getStyle("A4:M{$lastRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle("A4:M4")->getFont()->setBold(true);
            }
        ];
    }
}