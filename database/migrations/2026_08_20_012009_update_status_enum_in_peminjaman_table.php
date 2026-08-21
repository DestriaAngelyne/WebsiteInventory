<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE peminjaman MODIFY COLUMN status ENUM('pending', 'disetujui', 'ditolak', 'dikembalikan', 'selesai', 'dibatalkan') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE peminjaman MODIFY COLUMN status ENUM('pending', 'disetujui', 'ditolak', 'dikembalikan', 'selesai') DEFAULT 'pending'");
    }
};
