<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('park_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'started_at']);
            $table->index(['user_id', 'park_id']);
        });

        Schema::create('visit_pois', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('point_of_interest_id')->constrained('points_of_interest')->cascadeOnDelete();
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->unique(['visit_id', 'point_of_interest_id'], 'visit_pois_unique');
        });

        Schema::create('photos', function (Blueprint $table): void {
            $table->id();
            $table->morphs('photoable');
            $table->string('disk', 32);
            $table->string('path', 1024);
            $table->string('original_filename');
            $table->string('mime', 64);
            $table->unsignedBigInteger('size');
            $table->timestamp('taken_at')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->foreignId('uploaded_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('share_tokens', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('token', 64)->unique();
            $table->timestamp('revoked_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('share_tokens');
        Schema::dropIfExists('photos');
        Schema::dropIfExists('visit_pois');
        Schema::dropIfExists('visits');
    }
};
