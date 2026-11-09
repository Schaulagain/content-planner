<?php

namespace App\Models;

use App\Enums\Platform;
use App\Enums\Stage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/** An idea once it has gone up, on one or more platforms. */
#[Fillable(['idea_id', 'posted_at', 'caption', 'series_number', 'reminded_at'])]
class Post extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'posted_at' => 'datetime',
            'reminded_at' => 'datetime',
        ];
    }

    public function idea(): BelongsTo
    {
        return $this->belongsTo(Idea::class);
    }

    public function stats(): HasMany
    {
        return $this->hasMany(Stats::class)->orderBy('id');
    }

    /**
     * Records that an idea went up: the caption used, the platforms, and its
     * number in its series.
     *
     * @param  list<Platform>  $platforms
     */
    public static function publish(Idea $idea, string $caption, array $platforms, \DateTimeInterface $postedAt): self
    {
        return DB::transaction(function () use ($idea, $caption, $platforms, $postedAt): self {
            $post = self::create([
                'idea_id' => $idea->id,
                // Stored in UTC like every other time, whatever zone it was typed in.
                'posted_at' => Carbon::instance($postedAt)->utc(),
                'caption' => $caption,
                'series_number' => $idea->series?->nextNumber(),
            ]);

            foreach ($platforms as $platform) {
                $post->stats()->create(['platform' => $platform]);
            }

            $idea->update(['stage' => Stage::Posted]);

            return $post;
        });
    }

    /** Whether every platform's stats have been written down. */
    public function hasAllStats(): bool
    {
        return $this->stats->every(fn (Stats $stats) => $stats->recorded_at !== null);
    }

    public function statsAreDue(): bool
    {
        return $this->posted_at->lte(self::statsDueBefore());
    }

    /** Posts old enough for their stats to be worth writing down. */
    #[Scope]
    protected function statsDue(Builder $query): void
    {
        $query->where('posted_at', '<=', self::statsDueBefore());
    }

    /** When it went up, in the planner's time zone. */
    public function postedAtLocal(): Carbon
    {
        return $this->posted_at->copy()->timezone(config('planner.timezone'));
    }

    private static function statsDueBefore(): Carbon
    {
        return now()->subHours(config('planner.stats_after_hours'));
    }
}
