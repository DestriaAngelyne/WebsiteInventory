<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->enum('kondisi_pengembalian', ['baik', 'rusak_ringan', 'rusak_berat', 'hilang'])->nullable()->after('denda');
            $table->unsignedBigInteger('biaya_ganti')->default(0)->after('kondisi_pengembalian');
        });
    }

    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn(['kondisi_pengembalian', 'biaya_ganti']);
        });
    }
};
