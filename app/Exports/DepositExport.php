<?php

namespace App\Exports;

use App\Models\Deposit;
use Maatwebsite\Excel\Concerns\{
    FromCollection,
    WithHeadings,
    ShouldAutoSize
};

class DepositExport implements FromCollection, WithHeadings, ShouldAutoSize
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

        return $query->get();
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
            '10 Coin',
            '5 Coin',
            '1 Coin',
            'Actual Deposit',
            'Variance'
        ];
    }
}