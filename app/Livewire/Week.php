<?php

namespace App\Livewire;

use App\Models\Post;
use App\Models\Review;
use App\Support\WeekRange;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('This week')]
class Week extends Component
{
    public string $learning = '';

    public bool $saved = false;

    public function mount(): void
    {
        $this->learning = (string) Review::forWeek(WeekRange::current()->start)?->learning;
    }

    public function save(): void
    {
        $this->validate(['learning' => ['required', 'string', 'max:500']]);

        Review::saveForWeek(WeekRange::current()->start, trim($this->learning));

        $this->saved = true;
    }

    public function render()
    {
        $week = WeekRange::current();

        $posts = Post::query()
            ->with(['idea.series', 'stats'])
            ->whereBetween('posted_at', [$week->start->utc(), $week->end->utc()])
            ->orderBy('posted_at')
            ->get();

        return view('livewire.week', [
            'week' => $week,
            'posts' => $posts,
            'target' => config('planner.posts_per_week'),
            'learnings' => Review::query()
                ->whereDate('week_start', '<', $week->start->toDateString())
                ->latest('week_start')
                ->get(),
        ]);
    }
}
