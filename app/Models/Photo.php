<?php

declare(strict_types=1);

namespace App\Models;

use App\Domain\Coordinates;
use Database\Factories\PhotoFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $photoable_type
 * @property int $photoable_id
 * @property string $disk
 * @property string $path
 * @property string $original_filename
 * @property string $mime
 * @property int $size
 * @property Carbon|null $taken_at
 * @property float|null $latitude
 * @property float|null $longitude
 * @property int $uploaded_by_user_id
 */
class Photo extends Model
{
    /** @use HasFactory<PhotoFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'taken_at' => 'datetime',
            'latitude' => 'float',
            'longitude' => 'float',
            'size' => 'integer',
        ];
    }

    /** @return MorphTo<Model, $this> */
    public function photoable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    /** @return Attribute<Coordinates|null, never> */
    protected function coordinates(): Attribute
    {
        return Attribute::make(
            get: fn (): ?Coordinates => $this->latitude !== null && $this->longitude !== null
                ? new Coordinates((float) $this->latitude, (float) $this->longitude)
                : null,
        );
    }
}
