<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
        });

        $indexes = collect(Schema::getIndexes('users'))->pluck('name')->toArray();
        if (!in_array('users_phone_unique', $indexes)) {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('phone');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable(false)->change();
        });

        $indexes = collect(Schema::getIndexes('users'))->pluck('name')->toArray();
        if (in_array('users_phone_unique', $indexes)) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_phone_unique');
            });
        }
    }
};
