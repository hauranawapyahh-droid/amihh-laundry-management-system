<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('pemulihan_otp')) {
            Schema::create('pemulihan_otp', function (Blueprint $t) {
                $t->id();
                $t->string('no_telepon', 20)->index();
                $t->string('kode_hash', 64);
                $t->unsignedTinyInteger('percobaan')->default(0);
                $t->timestamp('kedaluwarsa')->nullable();
                $t->timestamp('diverifikasi_pada')->nullable();
                $t->string('token_hash', 64)->nullable()->index();
                $t->timestamp('created_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pemulihan_otp');
    }
};