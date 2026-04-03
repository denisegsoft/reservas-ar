<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Agregar 'user' al enum manteniendo los valores viejos
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'owner', 'client', 'user') NOT NULL DEFAULT 'user'");
        // 2. Migrar owner y client a user
        DB::statement("UPDATE users SET role = 'user' WHERE role IN ('owner', 'client')");
        // 3. Quitar valores obsoletos del enum
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'user') NOT NULL DEFAULT 'user'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'owner', 'client') NOT NULL DEFAULT 'client'");
    }
};
