<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'kode_transaksi',
        'id_pelanggan',
        'id_kasir',
        'id_verifikator',
        'total_harga',
        'status_pembayaran',
        'status_pengambilan',
        'waktu_pengambilan',
        'catatan',
    ];

    protected $casts = [
        'total_harga' => 'decimal:2',
        'waktu_pengambilan' => 'datetime',
    ];

    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function kasir()
    {
        return $this->belongsTo(User::class, 'id_kasir', 'id_user');
    }

    public function verifikator()
    {
        return $this->belongsTo(User::class, 'id_verifikator', 'id_user');
    }

    public function detail()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi', 'id_transaksi');
    }

    public function pembayaran()
    {
        return $this->hasMany(Pembayaran::class, 'id_transaksi', 'id_transaksi');
    }

    public function prosesCucian()
    {
        return $this->hasMany(ProsesCucian::class, 'id_transaksi', 'id_transaksi');
    }
}