<?php

namespace App\Models;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'telegram_chat_id',
        'telegram_bot_token',
        'password',
        'role',
        'parent_admin_id',
        'face_photo',
        'prtg_url',
        'prtg_username',
        'prtg_password',
        'timezone',
        'customer_limit',
        'enable_teknisi_payment',
        'smtp_host',
        'smtp_port',
        'smtp_username',
        'smtp_password',
        'smtp_encryption',
        'smtp_from_address',
        'smtp_from_name',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'prtg_password' => 'encrypted',
            'mikrotik_password' => 'encrypted',
            'telegram_bot_token' => 'encrypted',
            'smtp_password' => 'encrypted',
        ];
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function parentAdmin()
    {
        return $this->belongsTo(User::class, 'parent_admin_id');
    }

    public function subUsers()
    {
        return $this->hasMany(User::class, 'parent_admin_id');
    }

    public function presensis()
    {
        return $this->hasMany(Presensi::class);
    }
}
