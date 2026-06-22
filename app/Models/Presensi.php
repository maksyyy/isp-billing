<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $table = 'presensis';

    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_keluar',
        'foto_masuk',
        'foto_keluar',
        'status',
        'lembur'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
