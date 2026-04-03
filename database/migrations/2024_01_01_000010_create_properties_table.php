<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('short_description')->nullable();
            $table->string('address');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('price_per_day', 10, 2);
            $table->decimal('price_per_hour', 10, 2)->nullable();
            $table->decimal('price_weekend', 10, 2)->nullable();
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('bedrooms')->default(0);
            $table->unsignedInteger('bathrooms')->default(0);
            $table->unsignedInteger('parking_spots')->default(0);
            $table->json('amenities')->nullable();
            $table->string('cover_image')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->enum('type', ['quinta', 'salon', 'cancha', 'coworking', 'campo', 'otro'])->default('quinta');
            $table->time('available_from')->nullable();
            $table->time('available_to')->nullable();
            $table->boolean('featured')->default(false);
            $table->json('rules')->nullable();
            $table->unsignedInteger('min_days')->default(1);
            $table->timestamps();
        });

        Schema::create('property_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->string('path');
            $table->string('caption')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();
        });

        Schema::create('blocked_dates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained('properties')->onDelete('cascade');
            $table->date('date');
            $table->string('reason')->nullable();
            $table->timestamps();

            $table->unique(['property_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_dates');
        Schema::dropIfExists('property_images');
        Schema::dropIfExists('properties');
    }
};
