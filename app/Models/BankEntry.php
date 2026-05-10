<?php

namespace App\Models;

use App\Traits\Auditable;

class BankEntry extends Model
{
    use HasFactory, Auditable;
    protected $fillable = [
        'account_number',
        'bank_name',
        'cash_amount',
        'cash_type',
        'remarks',
        'exchange_id',
        'user_id',
        'status',
    ];
    public function exchange()
    {
        return $this->belongsTo(Exchange::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function Bank()
    {
        return $this->belongsTo(Bank::class);
    }
}
