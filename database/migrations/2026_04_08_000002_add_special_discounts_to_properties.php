<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // [{"date_from":"2024-12-24","date_to":"2024-12-31","discount":20,"label":"Navidad"}]
            $table->json('date_discounts')->nullable()->after('day_discounts');
            // [{"days":[1,2,3,4],"discount":10}]  (0=Dom,1=Lun,...,6=Sáb)
            $table->json('weekday_discounts')->nullable()->after('date_discounts');
        });
    }

    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            $table->dropColumn(['date_discounts', 'weekday_discounts']);
        });
    }
};
