<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->string('email_pemulihan', 255)->nullable()->unique();
            $table->timestamp('email_pemulihan_verified_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn([
                'email_pemulihan',
                'email_pemulihan_verified_at',
            ]);
        });
    }
};
