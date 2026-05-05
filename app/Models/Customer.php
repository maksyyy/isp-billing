<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
   protected $fillable = [
    'customer_code',
    'name',
    'phone',
    'address',
    'package_id'
];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function invoices()
    {
        return $this->hasMany(\App\Models\Invoice::class);
    }
}