<?php

namespace App\Models;

use App\Traits\Auditable;

class OwnerProfit extends Model
{
    use HasFactory, Auditable;
    protected $fillable = [
        'cash_amount',
        'remarks',
        'user_id',
        'exchange_id',
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
