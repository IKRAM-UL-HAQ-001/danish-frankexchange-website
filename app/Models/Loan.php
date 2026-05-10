<?php

namespace App\Models;

use App\Traits\Auditable;

class Loan extends Model
{
    use HasFactory, Auditable;
    protected $fillable = [
        'receiver_id',
        'cash_type',
        'cash_amount',
        'remarks',
        'exchange_id',
        'user_id',
    ];
    public function exchange()
    {
        return $this->belongsTo(Exchange::class);
    }
    public function receiverExchange()
    {
        return $this->belongsTo(Exchange::class, 'receiver_id');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
