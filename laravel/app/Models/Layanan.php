<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table = 'layanan';
    protected $primaryKey = 'id_layanan';

    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'nama_layanan',
        'jenis_satuan',
        'harga_per_satuan',
        'is_active',
        'id_dibuat_oleh',
    ];

    protected $casts = [
        'harga_per_satuan' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'id_dibuat_oleh', 'id_user');
    }

    public function detailTransaksi()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_layanan', 'id_layanan');
    }
}