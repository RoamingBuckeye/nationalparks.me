<?php

declare(strict_types=1);

namespace App\Models;

use App\Integrations\Nps\Enums\AlertCategory;
use Database\Factories\AlertFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $park_id
 * @property AlertCategory|null $category
 * @property string $title
 * @property string|null $description
 * @property string|null $url
 * @property Carbon|null $last_indexed_at
 * @property Carbon|null $archived_at
 */
class Alert extends Model
{
    /** @use HasFactory<AlertFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'category' => AlertCategory::class,
            'last_indexed_at' => 'datetime',
            'last_synced_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    /** @param Builder<Alert> $query */
    public function scopeActive(Builder $query): void
    {
        $query->whereNull('archived_at');
    }

    /** @return BelongsTo<Park, $this> */
    public function park(): BelongsTo
    {
        return $this->belongsTo(Park::class);
    }
}
