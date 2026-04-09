<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Horas a partir de las cuales se cobra precio por día en vez de por hora
            $table->unsignedSmallInteger('hour_day_threshold')->nullable()->after('price_per_hour');
            // JSON: [{"days": 7, "discount": 10}, {"days": 15, "discount": 20}, ...]
            $table->json('day_discounts')->nullable()->after('hour_day_threshold');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['hour_day_threshold', 'day_discounts']);
        });
    }
};
