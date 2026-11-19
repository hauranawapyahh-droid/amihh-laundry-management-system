<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';

    protected $primaryKey = 'id_pelanggan';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_user',
        'nama',
        'no_telepon',
        'alamat',
        'email_pemulihan',
        'email_pemulihan_verified_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function transaksi()
    {
        return $this->hasMany(
            Transaksi::class,
            'id_pelanggan',
            'id_pelanggan'
        );
    }
}


