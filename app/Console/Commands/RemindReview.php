<?php

namespace App\Console\Commands;

use App\Models\Review;
use App\Support\Ntfy;
use App\Support\WeekRange;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reminders:review')]
#[Description('Nudge for the weekly review if this week has none yet')]
class RemindReview extends Command
{
    public function handle(Ntfy $ntfy): int
    {
        $done = Review::forWeek(WeekRange::current()->start) !== null;

        if (! $done) {
            $ntfy->send(
                'Time for the weekly review',
                'Fill in the numbers and write down one thing you learned this week.',
                tags: ['calendar'],
                click: route('week'),
            );
        }

        return self::SUCCESS;
    }
}
