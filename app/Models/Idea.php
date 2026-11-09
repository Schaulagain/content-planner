<?php

namespace App\Models;

use App\Enums\Stage;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

/** Something worth filming. Every video starts as one. */
#[Fillable(['place', 'caption', 'notes', 'series_id', 'stage', 'photo_path', 'latitude', 'longitude'])]
class Idea extends Model
{
    use HasFactory;

    protected $attributes = [
        'stage' => Stage::Idea->value,
    ];

    protected function casts(): array
    {
        return [
            'stage' => Stage::class,
            'latitude' => 'float',
            'longitude' => 'float',
        ];
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(Series::class);
    }

    public function post(): HasOne
    {
        return $this->hasOne(Post::class);
    }

    protected static function booted(): void
    {
        static::deleted(function (Idea $idea) {
            if ($idea->photo_path) {
                Storage::disk('public')->delete($idea->photo_path);
            }
        });
    }

    /** Whether it can still be marked as posted. */
    public function canPost(): bool
    {
        return $this->post === null;
    }

    public function photoUrl(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    /** "Boudhanath · Places that make you slow down #3", or just the place. */
    public function heading(): string
    {
        if (! $this->series) {
            return $this->place;
        }

        $number = $this->post?->series_number;

        return $this->place.' · '.$this->series->name.($number ? " #{$number}" : '');
    }
}
