<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'customer_id',
        'amount',
        'payment_date'
        
    ];

    public function invoice()
    {
        return $this->belongsTo(\App\Models\Invoice::class);
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }
}
