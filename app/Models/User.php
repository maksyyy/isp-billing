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
        'password',
        'role',
        'parent_admin_id',
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
}
