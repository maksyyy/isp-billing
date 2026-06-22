<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'admin_id',
        'customer_id',
        'amount',
        'due_date',
        'status',
        'paid_amount'
    ];

    public function payments()
    {
        return $this->hasMany(\App\Models\Payment::class);
    }

    public function customer()
    {
        return $this->belongsTo(\App\Models\Customer::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}