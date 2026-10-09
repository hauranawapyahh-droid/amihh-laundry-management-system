<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    protected $table = 'pembayaran';
    protected $primaryKey = 'id_pembayaran';

    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_transaksi',
        'metode_pembayaran',
        'jumlah_bayar',
        'kembalian',
        'status_konfirmasi',
        'catatan_pelunasan',
        'id_konfirmator',
        'waktu_konfirmasi',
    ];

    protected $casts = [
        'jumlah_bayar' => 'decimal:2',
        'kembalian' => 'decimal:2',
        'waktu_konfirmasi' => 'datetime',
    ];

    public function transaksi()
    {
        return $this->belongsTo(
            Transaksi::class,
            'id_transaksi',
            'id_transaksi'
        );
    }

    public function konfirmator()
    {
        return $this->belongsTo(
            User::class,
            'id_konfirmator',
            'id_user'
        );
    }
}