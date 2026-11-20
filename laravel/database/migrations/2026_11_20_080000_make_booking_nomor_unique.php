<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('booking')) {
            return;
        }
        DB::statement("UPDATE booking SET nomor_antrean = CONCAT('A-', DATE_FORMAT(tanggal_booking,'%d%m%y'), '-', LPAD(urutan,3,'0'))");
        $ada = DB::select("SHOW INDEX FROM booking WHERE Key_name = 'booking_nomor_antrean_unique'");
        if (!$ada) {
            Schema::table('booking', function (Blueprint $t) {
                $t->unique('nomor_antrean', 'booking_nomor_antrean_unique');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('booking')) {
            Schema::table('booking', function (Blueprint $t) {
                $t->dropUnique('booking_nomor_antrean_unique');
            });
        }
    }
};