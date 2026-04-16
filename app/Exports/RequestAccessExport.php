<?php

namespace App\Exports;

use App\Models\Deposit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RequestAccessExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithCustomStartCell
{
    protected $date;
    protected $branchId;

    public function __construct($date = null, $branchId = null)
    {
        $this->date = $date;
        $this->branchId = $branchId;
    }

    public function startCell(): string
    {
        return 'A5';
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
                $row->branch,
                $row->deposit_date,
                number_format($row->net_amount, 2),
                number_format($row->actual_amount, 2),
                number_format($row->variance, 2),
                $row->closed_by,
                date('M d, Y h:i A', strtotime($row->created_at)),
                ucfirst($row->status),
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
        $sheet->setCellValue('A1', 'NICOLE TILES CENTER');
        $sheet->setCellValue('A2', 'Request Access Report');
        $sheet->setCellValue('A3', 'Generated: ' . now()->format('M d, Y h:i A'));

        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        $sheet->mergeCells('A3:H3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);
        $sheet->getStyle('A3')->getFont()->setSize(11);

        $sheet->getStyle('A5:H5')->getFont()->setBold(true);

        return [];
    }
}