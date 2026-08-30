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
        Schema::create('media_enhancements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('type', 30);
            $table->string('provider', 30);
            $table->string('status', 30)->default('pending');
            $table->json('params')->nullable();
            $table->string('result_key', 255)->nullable();
            $table->string('result_url', 500)->nullable();
            $table->text('error')->nullable();
            $table->unsignedSmallInteger('cost_units')->default(0);
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['media_id', 'status']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_enhancements');
    }
};
