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
        Schema::table('reservations', function (Blueprint $table) {
            $table->timestamp('checkin_reminder_sent_at')->nullable()->after('cancelled_at');
            $table->timestamp('review_requested_at')->nullable()->after('checkin_reminder_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['checkin_reminder_sent_at', 'review_requested_at']);
        });
    }
};
