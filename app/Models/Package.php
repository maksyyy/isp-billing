<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    protected $fillable = ['admin_id', 'name', 'price', 'speed'];

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
