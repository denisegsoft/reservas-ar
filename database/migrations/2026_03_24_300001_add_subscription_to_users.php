<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('subscription_paid')->default(false)->after('deleted');
            $table->timestamp('subscription_paid_at')->nullable()->after('subscription_paid');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['subscription_paid', 'subscription_paid_at']);
        });
    }
};
