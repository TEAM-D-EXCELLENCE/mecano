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
        Schema::create('car_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained('cars')->cascadeOnDelete();
            $table->string('type', 30);
            $table->char('ip_hash', 64)->nullable();
            $table->string('referer', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Indexes for aggregation and retention purge (12 months)
            $table->index(['car_id', 'type', 'created_at']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('car_events');
    }
};
