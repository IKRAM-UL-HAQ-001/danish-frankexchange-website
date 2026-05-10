<?php

namespace App\Models;

use App\Traits\Auditable;

class VenderPayment extends Model
{
    use HasFactory, Auditable;
    protected $fillable = ['paid_amount', 'remaining_amount', 'payment_type','exchange_id','user_id','remarks'];
    
    public function exchange()
    {
        return $this->belongsTo(Exchange::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
