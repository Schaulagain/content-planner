<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

/** The weekly look back, and the one line it leaves behind. */
#[Fillable(['week_start', 'learning'])]
class Review extends Model
{
    protected function casts(): array
    {
        return ['week_start' => 'date'];
    }

    public static function forWeek(\DateTimeInterface $start): ?self
    {
        return self::query()->whereDate('week_start', $start->format('Y-m-d'))->first();
    }

    public static function saveForWeek(\DateTimeInterface $start, string $learning): self
    {
        $review = self::forWeek($start) ?? new self(['week_start' => $start->format('Y-m-d')]);
        $review->fill(['learning' => $learning])->save();

        return $review;
    }
}
