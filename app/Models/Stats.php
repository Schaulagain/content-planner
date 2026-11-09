<?php

namespace App\Models;

use App\Enums\Platform;
use App\Enums\Stage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** The numbers one platform shows for one post, written down after about 48 hours. */
#[Fillable(['post_id', 'platform', 'views', 'likes', 'comments', 'shares', 'saves', 'average_watch_seconds', 'recorded_at'])]
class Stats extends Model
{
    use HasFactory;

    protected $table = 'stats';

    public const NUMBERS = ['views', 'likes', 'comments', 'shares', 'saves', 'average_watch_seconds'];

    protected function casts(): array
    {
        return [
            'platform' => Platform::class,
            'average_watch_seconds' => 'float',
            'recorded_at' => 'datetime',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Whether any of the numbers was filled in.
     *
     * @param  array<string, mixed>  $numbers
     */
    public static function anyFilled(array $numbers): bool
    {
        return collect($numbers)->only(self::NUMBERS)->contains(fn ($value) => $value !== null && $value !== '');
    }

    /**
     * Saves the numbers, and moves the idea to reviewed once every platform has them.
     *
     * @param  array<string, int|float|null>  $numbers
     */
    public function record(array $numbers): void
    {
        $this->update([...array_intersect_key($numbers, array_flip(self::NUMBERS)), 'recorded_at' => now()]);

        $post = $this->post->load('stats');

        if ($post->hasAllStats()) {
            $post->idea->update(['stage' => Stage::Reviewed]);
        }
    }
}
