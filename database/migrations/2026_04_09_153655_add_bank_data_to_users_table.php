<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bank_holder', 255)->nullable()->after('website');
            $table->string('bank_cbu', 22)->nullable()->after('bank_holder');
            $table->string('bank_alias', 100)->nullable()->after('bank_cbu');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['bank_holder', 'bank_cbu', 'bank_alias']);
        });
    }
};
