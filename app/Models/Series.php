<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** A named run of related videos, numbered in the order they're posted. */
#[Fillable(['name'])]
class Series extends Model
{
    use HasFactory;

    protected $table = 'series';

    public function ideas(): HasMany
    {
        return $this->hasMany(Idea::class);
    }

    /** The number the next post in this series gets. */
    public function nextNumber(): int
    {
        return Post::query()
            ->whereHas('idea', fn ($query) => $query->where('series_id', $this->id))
            ->max('series_number') + 1;
    }
}
