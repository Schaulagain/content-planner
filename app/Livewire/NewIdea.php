<?php

namespace App\Livewire;

use App\Models\Idea;
use App\Models\Series;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
#[Title('New idea')]
class NewIdea extends Component
{
    use WithFileUploads;

    public string $place = '';

    public string $caption = '';

    public string $notes = '';

    public ?int $seriesId = null;

    public string $newSeries = '';

    public $photo;

    public ?float $latitude = null;

    public ?float $longitude = null;

    public function save()
    {
        $this->validate([
            'place' => ['required', 'string', 'max:120'],
            'caption' => ['nullable', 'string', 'max:300'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'seriesId' => ['nullable', 'exists:series,id'],
            'newSeries' => ['nullable', 'string', 'max:80'],
            // The page shrinks photos to about 1200 px before sending them.
            'photo' => ['nullable', 'image', 'max:8192'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        $seriesId = filled($this->newSeries)
            ? Series::firstOrCreate(['name' => trim($this->newSeries)])->id
            : $this->seriesId;

        Idea::create([
            'place' => trim($this->place),
            'caption' => trim($this->caption) ?: null,
            'notes' => trim($this->notes) ?: null,
            'series_id' => $seriesId,
            'photo_path' => $this->photo?->store('ideas', 'public'),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);

        return $this->redirectRoute('board', navigate: true);
    }

    public function render()
    {
        return view('livewire.new-idea', ['series' => Series::orderBy('name')->get()]);
    }
}
