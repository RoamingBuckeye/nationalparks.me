<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('points_of_interest', function (Blueprint $table): void {
            $table->id();
            $table->uuid('nps_id')->unique();
            $table->foreignId('park_id')->constrained()->cascadeOnDelete();
            $table->string('kind', 32);
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('url')->nullable();
            $table->json('tags');
            $table->json('amenities');
            $table->boolean('is_passport_stamp_location')->default(false);
            $table->json('details');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->index(['park_id', 'kind']);
            $table->index('archived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('points_of_interest');
    }
};
