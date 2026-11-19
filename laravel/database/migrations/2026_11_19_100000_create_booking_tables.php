<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('booking')) {
            Schema::create('booking', function (Blueprint $t) {
                $t->id('id_booking');
                $t->unsignedBigInteger('id_pelanggan')->index();
                $t->date('tanggal_booking');
                $t->unsignedInteger('urutan');
                $t->string('nomor_antrean', 20);
                $t->string('status', 20)->default('MENUNGGU')->index();
                $t->text('catatan')->nullable();
                $t->string('alasan_batal', 255)->nullable();
                $t->unsignedBigInteger('id_transaksi')->nullable()->unique();
                $t->timestamps();
                $t->unique(['tanggal_booking', 'urutan']);
            });
        }
        if (!Schema::hasTable('booking_detail')) {
            Schema::create('booking_detail', function (Blueprint $t) {
                $t->id('id_booking_detail');
                $t->unsignedBigInteger('id_booking')->index();
                $t->unsignedBigInteger('id_layanan');
                $t->decimal('jumlah', 10, 2);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_detail');
        Schema::dropIfExists('booking');
    }
};