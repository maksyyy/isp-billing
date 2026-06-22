<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BackboneDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'admin_id',
        'name',
        'ip',
        'status',
        'last_ping_at',
        'first_failed_at',
        'telegram_alert_sent',
    ];

    protected $casts = [
        'last_ping_at' => 'datetime',
        'first_failed_at' => 'datetime',
        'telegram_alert_sent' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
