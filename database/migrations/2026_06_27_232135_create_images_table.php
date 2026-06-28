<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('images', function (Blueprint $table): void {
            $table->id();
            $table->morphs('imageable');
            $table->string('url', 1024);
            $table->string('title')->nullable();
            $table->string('alt_text', 1024)->nullable();
            $table->text('caption')->nullable();
            $table->string('credit')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            // URL uniqueness per imageable is enforced in the upsert action — MySQL's
            // 3072-byte key limit can't index (imageable_type + imageable_id + url(1024)).
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('images');
    }
};
