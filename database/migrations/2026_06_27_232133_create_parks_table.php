<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parks', function (Blueprint $table): void {
            $table->id();
            $table->uuid('nps_id')->unique();
            $table->string('park_code', 16)->unique();
            $table->string('name');
            $table->string('full_name');
            $table->string('designation');
            $table->text('description');
            $table->string('url');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('states');
            $table->text('directions_info')->nullable();
            $table->string('directions_url')->nullable();
            $table->text('weather_info')->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parks');
    }
};
