<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/** A Monday-to-Sunday week in the planner's time zone. */
final readonly class WeekRange
{
    private function __construct(public CarbonImmutable $start, public CarbonImmutable $end) {}

    public static function current(): self
    {
        $start = CarbonImmutable::now(config('planner.timezone'))->startOfWeek(CarbonImmutable::MONDAY);

        return new self($start, $start->endOfWeek(CarbonImmutable::SUNDAY));
    }
}
