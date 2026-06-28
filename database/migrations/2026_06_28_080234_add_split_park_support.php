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
            $table->string('nps_source_code', 16)->nullable()->after('park_code');
            $table->uuid('nps_source_id')->nullable()->after('nps_source_code');
            $table->index('nps_source_code');
            $table->dropUnique(['nps_id']);
            $table->index('nps_id');
        });

        Schema::table('points_of_interest', function (Blueprint $table): void {
            $table->dropUnique(['nps_id']);
            $table->index('nps_id');
            $table->unique(['nps_id', 'park_id'], 'points_of_interest_nps_id_park_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('points_of_interest', function (Blueprint $table): void {
            $table->dropUnique('points_of_interest_nps_id_park_id_unique');
            $table->dropIndex(['nps_id']);
            $table->unique('nps_id');
        });

        Schema::table('parks', function (Blueprint $table): void {
            $table->dropIndex(['nps_id']);
            $table->unique('nps_id');
            $table->dropIndex(['nps_source_code']);
            $table->dropColumn(['nps_source_code', 'nps_source_id']);
        });
    }
};
