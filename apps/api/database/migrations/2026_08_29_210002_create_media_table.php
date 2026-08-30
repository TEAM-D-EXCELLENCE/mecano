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
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('car_id')->constrained('cars')->cascadeOnDelete();
            $table->string('kind', 20);
            $table->string('role', 30);
            $table->string('provider', 30);
            $table->string('storage_key', 255);
            $table->string('url', 500);
            $table->string('published_url', 500)->nullable();
            $table->string('mime', 60);
            $table->unsignedInteger('bytes');
            $table->unsignedSmallInteger('width')->nullable();
            $table->unsignedSmallInteger('height')->nullable();
            $table->unsignedSmallInteger('duration_s')->nullable();
            $table->unsignedSmallInteger('position')->default(0);
            $table->string('alt', 200)->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();

            // Generated column & unique key for exclusive roles (main, video_interior, video_exterior)
            // Ensures exactly 1 main photo, 1 interior video, 1 exterior video per car
            $table->string('exclusive_role', 30)
                ->storedAs("CASE WHEN role IN ('main', 'video_interior', 'video_exterior') THEN role ELSE NULL END")
                ->nullable();
            $table->unique(['car_id', 'exclusive_role'], 'uq_car_exclusive_role');

            // Indexes
            $table->index(['car_id', 'kind', 'position']);
            $table->index('confirmed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
