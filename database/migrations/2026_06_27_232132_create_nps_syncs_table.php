<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nps_syncs', function (Blueprint $table): void {
            $table->id();
            $table->string('entity', 32);
            $table->string('park_code', 16)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->timestamp('succeeded_at')->nullable();
            $table->unsignedInteger('records_processed')->default(0);
            $table->text('last_error')->nullable();
            $table->timestamps();

            $table->index(['entity', 'park_code']);
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nps_syncs');
    }
};
