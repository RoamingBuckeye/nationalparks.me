<?php

declare(strict_types=1);

namespace App\Models;

use App\Integrations\Nps\Enums\NpsEntity;
use Illuminate\Database\Eloquent\Model;

class NpsSync extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'entity' => NpsEntity::class,
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'succeeded_at' => 'datetime',
        ];
    }
}
