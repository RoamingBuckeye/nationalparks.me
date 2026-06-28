<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('activities', function (Blueprint $table): void {
            $table->id();
            $table->string('nps_id', 36)->nullable()->index();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('topics', function (Blueprint $table): void {
            $table->id();
            $table->string('nps_id', 36)->nullable()->index();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('amenities', function (Blueprint $table): void {
            $table->id();
            $table->string('nps_id', 36)->nullable()->index();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->json('categories')->nullable();
            $table->timestamps();
        });

        Schema::create('activatables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->morphs('activatable');
            $table->unique(['activity_id', 'activatable_type', 'activatable_id'], 'activatables_unique');
        });

        Schema::create('topicables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('topic_id')->constrained()->cascadeOnDelete();
            $table->morphs('topicable');
            $table->unique(['topic_id', 'topicable_type', 'topicable_id'], 'topicables_unique');
        });

        Schema::create('taggables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->morphs('taggable');
            $table->unique(['tag_id', 'taggable_type', 'taggable_id'], 'taggables_unique');
        });

        Schema::create('amenitiables', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->morphs('amenitiable');
            $table->unique(['amenity_id', 'amenitiable_type', 'amenitiable_id'], 'amenitiables_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amenitiables');
        Schema::dropIfExists('taggables');
        Schema::dropIfExists('topicables');
        Schema::dropIfExists('activatables');
        Schema::dropIfExists('amenities');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('topics');
        Schema::dropIfExists('activities');
    }
};
