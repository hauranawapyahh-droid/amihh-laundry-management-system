<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE pembayaran MODIFY status_konfirmasi ENUM('PENDING','CONFIRMED','REJECTED') NOT NULL DEFAULT 'PENDING'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE pembayaran MODIFY status_konfirmasi ENUM('PENDING','CONFIRMED') NOT NULL DEFAULT 'PENDING'");
    }
};