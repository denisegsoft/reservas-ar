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
            $table->string('invoice_path')->nullable()->after('review_requested_at');
            $table->timestamp('invoice_uploaded_at')->nullable()->after('invoice_path');
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropColumn(['invoice_path', 'invoice_uploaded_at']);
        });
    }
};
