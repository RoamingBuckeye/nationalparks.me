<?php

declare(strict_types=1);

namespace App\Models;

use App\Integrations\Nps\Enums\AlertCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Alert extends Model
{
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

    /** @return BelongsTo<Park, $this> */
    public function park(): BelongsTo
    {
        return $this->belongsTo(Park::class);
    }
}
