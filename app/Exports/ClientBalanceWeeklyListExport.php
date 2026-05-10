<?php

namespace App\Exports;

use App\Models\ClientBalance;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Carbon\Carbon;
use Auth;

class ClientBalanceWeeklyListExport implements FromQuery, WithHeadings, WithStyles, WithColumnWidths
{
    use Exportable;

    protected $exchangeId;

    public function __construct($exchangeId)
    {
        $this->exchangeId = $exchangeId;
    }

    public function query()
    {
        $startOfWeek = Carbon::now()->startOfWeek(); // Start of the week (Monday)
        $endOfWeek = Carbon::now()->endOfWeek(); // End of the week (Sunday) 

        $query = ClientBalance::selectRaw('
                client_balances.id, 
                exchanges.name AS exchange_name,
                users.name AS user_name,
                client_balances.client_balance,
                client_balances.remarks,
                DATE_FORMAT(CONVERT_TZ(client_balances.created_at, "+00:00", "+05:30"), "%Y-%m-%d %H:%i:%s") AS created_at,
                DATE_FORMAT(CONVERT_TZ(client_balances.updated_at, "+00:00", "+05:30"), "%Y-%m-%d %H:%i:%s") AS updated_at
            ')
            ->join('exchanges', 'client_balances.exchange_id', '=', 'exchanges.id')
            ->join('users', 'client_balances.user_id', '=', 'users.id')
            ->whereBetween('client_balances.created_at', [$startOfWeek, $endOfWeek]) // Filter by the week range
            ->distinct();

        // Check if the query returns any results
        if ($query->count() === 0) {
            return collect(); // Return an empty collection if no records found
        }

        switch (Auth::user()->role) {
            case "exchange":
                return $query->where('client_balances.exchange_id', $this->exchangeId);
            case "admin":
            case "assistant":
                return $query; // Admin and assistant can see all
            default:
                return collect(); // Return an empty collection for unrecognized roles
        }
    }

    public function headings(): array
    {
        return [
            'ID',
            'Exchange Name',
            'User Name',
            'Client Balance',
            'Remarks',
            'Created At',
            'Updated At',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->getFont()->setBold(true);
        $sheet->getStyle('A1:G1')->getFont()->setSize(12);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10, // ID
            'B' => 20, // Exchange Name
            'C' => 20, // User Name
            'D' => 30, // Total Amount
            'E' => 30, // Total Amount
            'F' => 30, // Created At
            'G' => 30, // Updated At
        ];
    }
}
