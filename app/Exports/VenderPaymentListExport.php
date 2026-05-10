<?php

namespace App\Exports;

use App\Models\VenderPayment;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class VenderPaymentListExport implements FromQuery, WithHeadings, WithStyles, WithColumnWidths
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

    // public function query()
    // {
    //     $query =  VenderPayment::selectRaw('
    //         vender_payments.id, 
    //         vender_payments.paid_amount,
    //         vender_payments.remaining_amount,
    //         vender_payments.payment_type,
    //         exchanges.name AS exchange_name,
    //         vender_payments.remarks,
    //         DATE_FORMAT(vender_payments.created_at, "%Y-%m-%d %H:%i:%s") as created_at,
    //         DATE_FORMAT(vender_payments.updated_at, "%Y-%m-%d %H:%i:%s") as updated_at
    //     ')
    //     ->join('exchanges', 'vender_payments.exchange_id', '=', 'exchanges.id')
    //     ->whereDate('vender_payments.created_at', '>=', $this->startDate)
    //     ->whereDate('vender_payments.created_at', '<=', $this->endDate)
    //     ->where('vender_payments.exchange_id', $this->exchangeId);
    
    //     return $query;
    // }
    public function query()
    {
        $query = VenderPayment::selectRaw('
            vender_payments.id, 
            vender_payments.paid_amount,
            vender_payments.remaining_amount,
            vender_payments.payment_type,
            exchanges.name AS exchange_name,
            vender_payments.remarks,
            DATE_FORMAT(vender_payments.created_at, "%Y-%m-%d %H:%i:%s") as created_at,
            DATE_FORMAT(vender_payments.updated_at, "%Y-%m-%d %H:%i:%s") as updated_at
        ')
        ->join('exchanges', 'vender_payments.exchange_id', '=', 'exchanges.id')
        ->whereDate('vender_payments.created_at', '>=', $this->startDate)
        ->whereDate('vender_payments.created_at', '<=', $this->endDate);

        if (!in_array($this->exchangeId, [null, 'null', 0, '0', ''], true)) {
            $query->where('vender_payments.exchange_id', $this->exchangeId);
        }

        return $query;
    }


    public function headings(): array
    {
        return [
            'ID',
            'Paid Amount',
            'Remaining Amount',
            'Payment Type',
            'Exchange Name',
            'Remarks',
            'Created At',
            'Updated At',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->getFont()->setBold(true);
        $sheet->getStyle('A1:H1')->getFont()->setSize(12);
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,  // ID
            'B' => 20,  // Paid Amount
            'C' => 20,  // Remaining Amount
            'D' => 20,  // Payment Type
            'E' => 30,  // Exchange Name
            'F' => 30,  // Remarks
            'G' => 30,  // Created At
            'H' => 30,  // Updated At
        ];
    }
}
