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
use PhpOffice\PhpSpreadsheet\Style\Alignment;

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

        $row = $query->where('deposits.status', 'closed')
            ->latest('deposits.id')
            ->first();

        if (!$row) {
            return collect([]);
        }

        return collect([
            ['NICOLE TILES CENTER'],
            ['CASH DEPOSIT SLIP'],
            [''],
            ['Branch', $row->branch, 'Date', $row->deposit_date],
            ['Deposited By', auth()->user()->name],
            [''],
            ['Denomination', 'Qty', 'Amount'],
            ['1000', $row->denom_1000, $row->denom_1000 * 1000],
            ['500', $row->denom_500, $row->denom_500 * 500],
            ['200', $row->denom_200, $row->denom_200 * 200],
            ['100', $row->denom_100, $row->denom_100 * 100],
            ['50', $row->denom_50, $row->denom_50 * 50],
            ['20', $row->denom_20, $row->denom_20 * 20],
            ['10 Coin', $row->coin_10, $row->coin_10 * 10],
            ['5 Coin', $row->coin_5, $row->coin_5 * 5],
            ['1 Coin', $row->coin_1, $row->coin_1 * 1],
            [''],
            ['TOTAL CASH DEPOSIT', '', $row->actual_amount],
            ['Variance', '', $row->variance],
            [''],
            ['Depositor Signature', '', 'Received By'],
        ]);
    }

    public function headings(): array
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function ($event) {

                $sheet = $event->sheet->getDelegate();

                $sheet->mergeCells('A1:C1');
                $sheet->mergeCells('A2:C2');

                $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(16);
                $sheet->getStyle('A1:C2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('A7:C16')
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle('A7:C7')->getFont()->setBold(true);

                $sheet->getStyle('A18:C19')->getFont()->setBold(true);

                $sheet->getStyle('C8:C19')
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.00');

                $sheet->getStyle('A21:C21')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('A21')->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle('C21')->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle('A4:D5')->getFont()->setBold(true);
            }
        ];
    }
}