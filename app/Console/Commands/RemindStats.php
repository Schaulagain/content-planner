<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Support\Ntfy;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('reminders:stats')]
#[Description('Send one reminder for each post whose stats are due but not written down yet')]
class RemindStats extends Command
{
    public function handle(Ntfy $ntfy): int
    {
        $due = Post::query()
            ->with(['idea.series', 'stats'])
            ->whereNull('reminded_at')
            ->statsDue()
            ->whereHas('stats', fn ($query) => $query->whereNull('recorded_at'))
            ->get();

        foreach ($due as $post) {
            $sent = $ntfy->send(
                'Stats are due',
                'Add the numbers for: '.$post->idea->heading(),
                tags: ['bar_chart'],
                click: route('ideas.show', $post->idea),
            );

            // Left unmarked when nothing went out, so the next run tries again.
            if ($sent) {
                $post->update(['reminded_at' => now()]);
            }
        }

        return self::SUCCESS;
    }
}
