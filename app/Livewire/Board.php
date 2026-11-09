<?php

namespace App\Livewire;

use App\Enums\Stage;
use App\Models\Idea;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Board')]
class Board extends Component
{
    public function advance(int $id): void
    {
        $idea = Idea::findOrFail($id);

        if ($next = $idea->stage->next()) {
            $idea->update(['stage' => $next]);
        }
    }

    public function back(int $id): void
    {
        $idea = Idea::findOrFail($id);

        if ($previous = $idea->stage->previous()) {
            $idea->update(['stage' => $previous]);
        }
    }

    public function render()
    {
        $ideas = Idea::query()->with(['series', 'post'])->latest('updated_at')->get()->groupBy(fn (Idea $idea) => $idea->stage->value);

        return view('livewire.board', [
            'stages' => Stage::cases(),
            'ideas' => $ideas,
        ]);
    }
}
