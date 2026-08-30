<?php

declare(strict_types=1);

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
        Schema::create('cars', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 180)->unique();
            $table->foreignId('brand_id')->constrained('brands')->restrictOnDelete();
            $table->string('model', 120);
            $table->unsignedSmallInteger('year');
            $table->unsignedInteger('mileage_km');
            $table->unsignedBigInteger('price_xaf');
            $table->string('fuel', 30);
            $table->string('transmission', 30);
            $table->string('color', 40);
            $table->string('condition', 30);
            $table->text('description')->nullable();
            $table->string('status', 30)->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('whatsapp_clicks_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Catalog indexes (docs/01-architecture/03-modele-de-donnees.md)
            $table->index(['status', 'published_at']);
            $table->index(['brand_id', 'status']);
            $table->index('price_xaf');
            $table->index('year');
            $table->index(['status', 'is_featured', 'published_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cars');
    }
};
