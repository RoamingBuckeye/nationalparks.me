<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserStampFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * A stamp a user has earned. Row exists ⇔ earned; earning is sticky.
 *
 * @property int $id
 * @property int $user_id
 * @property int $stamp_id
 * @property Carbon $earned_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Stamp $stamp
 */
class UserStamp extends Model
{
    /** @use HasFactory<UserStampFactory> */
    use HasFactory;

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'earned_at' => 'datetime',
        ];
    }

    /**
     * Whether this award predates the stamp's last material change — i.e. it's
     * an earlier "vintage" edition, shown with the year it was earned.
     */
    public function isVintage(): bool
    {
        return $this->stamp->members_changed_at !== null
            && $this->earned_at->lessThan($this->stamp->members_changed_at);
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Stamp, $this> */
    public function stamp(): BelongsTo
    {
        return $this->belongsTo(Stamp::class);
    }
}
