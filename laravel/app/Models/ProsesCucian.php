<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProsesCucian extends Model
{
    protected $table = 'proses_cucian';
    protected $primaryKey = 'id_proses';

    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_transaksi',
        'id_staff',
        'status_proses',
        'catatan_proses',
    ];

    public function transaksi()
    {
        return $this->belongsTo(
            Transaksi::class,
            'id_transaksi',
            'id_transaksi'
        );
    }

    public function staff()
    {
        return $this->belongsTo(
            User::class,
            'id_staff',
            'id_user'
        );
    }
}