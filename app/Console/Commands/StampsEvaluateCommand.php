<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Stamps\EvaluateStamps;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class StampsEvaluateCommand extends Command
{
    protected $signature = 'stamps:evaluate
                            {--user= : Limit to a single user id}';

    protected $description = 'Award earned stamps to users who already qualify (backfill after adding or changing stamps)';

    public function handle(EvaluateStamps $evaluateStamps): int
    {
        $userId = $this->option('user');
        $awardedTotal = 0;
        $usersTouched = 0;

        User::query()
            ->when($userId !== null, fn ($query) => $query->whereKey($userId))
            ->chunkById(100, function (Collection $users) use ($evaluateStamps, &$awardedTotal, &$usersTouched): void {
                foreach ($users as $user) {
                    $awarded = $evaluateStamps($user);

                    if ($awarded->isEmpty()) {
                        continue;
                    }

                    $usersTouched++;
                    $awardedTotal += $awarded->count();
                    $this->line("  {$user->email}: +{$awarded->count()} stamp(s)");
                }
            });

        $this->info("Awarded {$awardedTotal} stamp(s) across {$usersTouched} user(s).");

        return self::SUCCESS;
    }
}
