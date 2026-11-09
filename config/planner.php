<?php

return [
    // Times on the page and the Sunday nudge use this zone.
    'timezone' => env('PLANNER_TIMEZONE', 'Asia/Kathmandu'),

    // How many posts a week the plan aims for.
    'posts_per_week' => (int) env('PLANNER_POSTS_PER_WEEK', 3),

    // How long after posting the stats are worth writing down.
    'stats_after_hours' => (int) env('PLANNER_STATS_AFTER_HOURS', 48),

    'ntfy' => [
        'url' => rtrim(env('NTFY_URL', 'https://ntfy.sh'), '/'),
        'topic' => env('NTFY_TOPIC'),
    ],
];
