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
        Schema::table('properties', function (Blueprint $table) {
            $table->string('street_name')->nullable()->after('address');
            $table->string('street_number')->nullable()->after('street_name');
            $table->string('locality')->nullable()->after('street_number');
            $table->string('partido')->nullable()->after('locality');
            $table->string('country')->default('Argentina')->after('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['street_name', 'street_number', 'locality', 'partido', 'country']);
        });
    }
};
