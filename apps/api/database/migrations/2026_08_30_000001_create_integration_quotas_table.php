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
        Schema::create('integration_quotas', function (Blueprint $table) {
            $table->id();
            $table->string('provider', 40);
            $table->char('period', 7); // Format: YYYY-MM
            $table->unsignedSmallInteger('used')->default(0);
            $table->unsignedSmallInteger('limit')->default(50);
            $table->timestamp('updated_at')->nullable();

            $table->unique(['provider', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('integration_quotas');
    }
};
