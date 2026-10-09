<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use PHPOpenSourceSaver\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id_user';

    public $incrementing = true;

    protected $keyType = 'int';

    const CREATED_AT = 'created_at';
    const UPDATED_AT = null;

    protected $fillable = [
        'nama',
        'username',
        'password_hash',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Password untuk Laravel Auth
     */
    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    /**
     * JWT Identifier
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * JWT Custom Claims
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * Relasi ke layanan yang dibuat user
     */
    public function layananDibuat()
    {
        return $this->hasMany(
            Layanan::class,
            'id_dibuat_oleh',
            'id_user'
        );
    }

    /**
     * Relasi transaksi sebagai kasir
     */
    public function transaksiKasir()
    {
        return $this->hasMany(
            Transaksi::class,
            'id_kasir',
            'id_user'
        );
    }

    /**
     * Relasi transaksi yang diverifikasi
     */
    public function transaksiDiverifikasi()
    {
        return $this->hasMany(
            Transaksi::class,
            'id_verifikator',
            'id_user'
        );
    }

    /**
     * Relasi proses cucian sebagai staff
     */
    public function prosesCucian()
    {
        return $this->hasMany(
            ProsesCucian::class,
            'id_staff',
            'id_user'
        );
    }

    /**
     * Relasi biaya operasional sebagai pencatat
     */
    public function biayaOperasional()
    {
        return $this->hasMany(
            BiayaOperasional::class,
            'id_pencatat',
            'id_user'
        );
    }
}