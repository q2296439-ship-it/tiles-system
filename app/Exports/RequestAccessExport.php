<?php

namespace App\Exports;

use App\Models\Deposit;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RequestAccessExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize
{
    protected $date;
    protected $branchId;

    public function __construct($date = null, $branchId = null)
    {
        $this->date = $date;
        $this->branchId = $branchId;
    }

    public function collection()
    {
        $query = Deposit::leftJoin('branches', 'deposits.branch_id', '=', 'branches.id')
            ->leftJoin('users', 'deposits.user_id', '=', 'users.id')
            ->select(
                'branches.name as branch',
                'deposits.deposit_date',
                'deposits.net_amount',
                'deposits.actual_amount',
                'deposits.variance',
                'users.name as closed_by',
                'deposits.created_at',
                'deposits.status'
            )
            ->where('deposits.status', 'closed');

        if ($this->date) {
            $query->whereDate('deposits.deposit_date', $this->date);
        }

        if ($this->branchId) {
            $query->where('deposits.branch_id', $this->branchId);
        }

        return $query->latest('deposits.id')->get()->map(function ($row) {
            return [
                'Branch'      => $row->branch,
                'Date Closed' => $row->deposit_date,
                'Net Amount'  => number_format($row->net_amount, 2),
                'Actual'      => number_format($row->actual_amount, 2),
                'Variance'    => number_format($row->variance, 2),
                'Closed By'   => $row->closed_by,
                'Closed At'   => date('M d, Y h:i A', strtotime($row->created_at)),
                'Status'      => ucfirst($row->status),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Branch',
            'Date Closed',
            'Net Amount',
            'Actual',
            'Variance',
            'Closed By',
            'Closed At',
            'Status',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
            ],
        ];
    }
}