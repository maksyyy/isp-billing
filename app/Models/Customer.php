<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'admin_id',
        'customer_code',
        'name',
        'phone',
        'ip',
        'address',
        'package_id',
        'is_active'
    ];

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function invoices()
    {
        return $this->hasMany(\App\Models\Invoice::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}