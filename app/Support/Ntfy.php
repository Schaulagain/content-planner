<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Sends a push notification to the ntfy app on my phone. Does nothing until
 * NTFY_TOPIC is set. Returns whether the notification went out.
 */
class Ntfy
{
    public function send(string $title, string $message, array $tags = [], ?string $click = null): bool
    {
        $topic = config('planner.ntfy.topic');

        if (! $topic) {
            return false;
        }

        try {
            Http::withHeaders([
                'Title' => $title,
                'Tags' => implode(',', $tags),
                ...($click ? ['Click' => $click] : []),
            ])->timeout(10)->withBody($message, 'text/plain')
                ->post(config('planner.ntfy.url').'/'.$topic)
                ->throw();

            return true;
        } catch (\Throwable $e) {
            Log::warning('ntfy failed: '.$e->getMessage());

            return false;
        }
    }
}
