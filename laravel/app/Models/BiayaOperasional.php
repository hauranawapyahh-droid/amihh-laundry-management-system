<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BiayaOperasional extends Model
{
    protected $table = 'biaya_operasional';
    protected $primaryKey = 'id_biaya';

    public $incrementing = true;
    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'id_pencatat',
        'nama_biaya',
        'kategori',
        'jumlah_biaya',
        'tanggal_biaya',
        'keterangan',
    ];

    protected $casts = [
        'jumlah_biaya' => 'decimal:2',
        'tanggal_biaya' => 'date',
    ];

    public function pencatat()
    {
        return $this->belongsTo(
            User::class,
            'id_pencatat',
            'id_user'
        );
    }
}