<?php

namespace App\Exports;

use App\Models\Loan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LoanListExport implements FromQuery, WithHeadings, WithStyles, WithColumnWidths
{
    use Exportable;

    protected $startDate;
    protected $endDate;
    protected $exchangeId;

    public function __construct($startDate, $endDate, $exchangeId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->exchangeId = $exchangeId;
    }

    public function query()
    {
        return Loan::query()
            ->selectRaw('
                loans.id,
                loans.cash_type,
                loans.cash_amount,
                loans.remarks,
                exchanges.name AS exchange_name,
                receiver_exchanges.name AS receiver_name,
                users.name AS user_name,
                DATE_FORMAT(loans.created_at, "%Y-%m-%d %H:%i:%s") as created_at
            ')
            ->join('exchanges', 'loans.exchange_id', '=', 'exchanges.id')
            ->leftJoin('exchanges AS receiver_exchanges', 'loans.receiver_id', '=', 'receiver_exchanges.id')
            ->join('users', 'loans.user_id', '=', 'users.id')
            ->whereDate('loans.created_at', '>=', $this->startDate)
            ->whereDate('loans.created_at', '<=', $this->endDate)
            ->when($this->exchangeId, function ($query) {
                $query->where('loans.exchange_id', $this->exchangeId);
            });
    }

    public function headings(): array
    {
        return [
            'Loan ID',
            'Cash Type',
            'Cash Amount',
            'Remarks',
            'Exchange Name',
            'Receiver Name',
            'User Name',
            'Created At',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Apply styling to the header row
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFont()->setSize(12);
    }

    public function columnWidths(): array
    {
        // Define column widths
        return [
            'A' => 10, // Loan ID
            'B' => 15, // Cash Type
            'C' => 20, // Cash Amount
            'D' => 30, // Remarks
            'E' => 25, // Exchange Name
            'F' => 25, // Receiver Name
            'G' => 20, // Created By
            'H' => 20, // Created At
        ];
    }
}
