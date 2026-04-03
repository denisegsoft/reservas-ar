<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('mp_preference_id')->nullable()->index();
            $table->string('mp_payment_id')->nullable()->index();
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('status', ['initiated', 'pending', 'approved', 'rejected', 'cancelled'])->default('initiated');
            $table->string('mp_status_detail')->nullable();    // ej: accredited, cc_rejected_insufficient_amount
            $table->string('payment_method')->nullable();      // ej: credit_card, debit_card
            $table->string('payment_type')->nullable();        // ej: credit_card, ticket
            $table->json('mp_response')->nullable();           // respuesta completa de MP
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_payments');
    }
};
