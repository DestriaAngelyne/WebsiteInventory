<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->date('tanggal_kembali_diminta')->nullable()->after('tanggal_kembali_rencana');
            $table->enum('status_perpanjangan', ['none', 'pending', 'disetujui', 'ditolak'])->default('none')->after('tanggal_kembali_diminta');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn(['tanggal_kembali_diminta', 'status_perpanjangan']);
        });
    }
};
