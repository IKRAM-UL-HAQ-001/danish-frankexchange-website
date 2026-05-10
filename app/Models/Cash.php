<?php

namespace App\Models;

use App\Traits\Auditable;

class Cash extends Model
{
    use HasFactory, Auditable;
    protected $fillable = [
        'reference_number', 'customer_name', 'cash_amount', 'cash_type',
        'bonus_amount', 'payment_type', 'remarks', 'user_id', 'exchange_id'
    ];

    public function exchange()
    {
        return $this->belongsTo(Exchange::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
