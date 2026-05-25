<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'title',
        'tanggal',
        'customer_id',
        'description',
        'assigned_to',
        'status',
        'bukti',
        'tanggal_selesai',
        'archived_at'
    ];

public function customer()
{
    return $this->belongsTo(Customer::class);
}

public function teknisi()
{
    return $this->belongsTo(User::class, 'assigned_to');
}
}