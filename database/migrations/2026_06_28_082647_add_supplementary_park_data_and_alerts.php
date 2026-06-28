<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('parks', function (Blueprint $table): void {
            $table->json('activities')->nullable()->after('weather_info');
            $table->json('topics')->nullable()->after('activities');
            $table->json('operating_hours')->nullable()->after('topics');
            $table->json('entrance_fees')->nullable()->after('operating_hours');
        });

        Schema::table('points_of_interest', function (Blueprint $table): void {
            $table->json('operating_hours')->nullable()->after('details');
        });

        Schema::create('alerts', function (Blueprint $table): void {
            $table->id();
            $table->uuid('nps_id');
            $table->foreignId('park_id')->constrained()->cascadeOnDelete();
            $table->string('park_code', 16);
            $table->string('category', 64)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->timestamp('last_indexed_at')->nullable();
            $table->timestamp('last_synced_at');
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();

            $table->unique(['nps_id', 'park_id'], 'alerts_nps_id_park_id_unique');
            $table->index('park_code');
            $table->index('category');
            $table->index('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');

        Schema::table('points_of_interest', function (Blueprint $table): void {
            $table->dropColumn('operating_hours');
        });

        Schema::table('parks', function (Blueprint $table): void {
            $table->dropColumn(['activities', 'topics', 'operating_hours', 'entrance_fees']);
        });
    }
};
