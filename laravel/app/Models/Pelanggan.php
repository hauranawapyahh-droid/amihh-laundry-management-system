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
        'nama',
        'no_telepon',
        'alamat',
    ];

    public function transaksi()
    {
        return $this->hasMany(
            Transaksi::class,
            'id_pelanggan',
            'id_pelanggan'
        );
    }
}