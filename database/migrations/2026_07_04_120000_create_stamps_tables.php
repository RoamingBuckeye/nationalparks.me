<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stamps', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();

            // StampCriteria: park_count | state_set | region_set
            $table->string('criteria_type');
            // park_count: how many distinct parks. state/region: null = all members, N = any N.
            $table->unsignedSmallInteger('required_count')->nullable();
            // state_set: a UsState code. region_set: a PassportRegion value. (else null)
            $table->string('state_code', 2)->nullable();
            $table->string('region')->nullable();

            $table->string('scene')->nullable();        // SVG scene key for the <Stamp> component
            $table->string('accent_color', 32)->nullable();
            $table->string('category')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);

            // Set when a collection's qualifying set materially changes; earners
            // before this timestamp hold a "vintage" edition (shown with a year).
            $table->timestamp('members_changed_at')->nullable();

            $table->timestamps();

            $table->index('criteria_type');
            $table->index(['is_active', 'sort_order']);
        });

        Schema::create('user_stamps', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('stamp_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at');
            $table->timestamps();

            $table->unique(['user_id', 'stamp_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_stamps');
        Schema::dropIfExists('stamps');
    }
};
