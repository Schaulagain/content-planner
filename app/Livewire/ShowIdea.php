<?php

namespace App\Livewire;

use App\Enums\Platform;
use App\Models\Idea;
use App\Models\Post;
use App\Models\Series;
use App\Models\Stats;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class ShowIdea extends Component
{
    public Idea $idea;

    public bool $editing = false;

    public string $place = '';

    public string $caption = '';

    public string $notes = '';

    public ?int $seriesId = null;

    public string $postCaption = '';

    /** @var list<string> */
    public array $platforms = ['tiktok'];

    public string $postedAt = '';

    /** @var array<string, array<string, int|float|string|null>> Numbers being typed in, by platform. */
    public array $numbers = [];

    public function mount(Idea $idea): void
    {
        $this->idea = $idea;
        $this->fillDetails();
        $this->postCaption = (string) $idea->caption;
        $this->postedAt = now(config('planner.timezone'))->format('Y-m-d\TH:i');
        $this->fillNumbers();
    }

    public function saveDetails(): void
    {
        $this->validate([
            'place' => ['required', 'string', 'max:120'],
            'caption' => ['nullable', 'string', 'max:300'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'seriesId' => ['nullable', 'exists:series,id'],
        ]);

        $this->idea->update([
            'place' => trim($this->place),
            'caption' => trim($this->caption) ?: null,
            'notes' => trim($this->notes) ?: null,
            'series_id' => $this->seriesId,
        ]);

        $this->editing = false;
    }

    public function cancelEditing(): void
    {
        $this->resetValidation();
        $this->fillDetails();
        $this->editing = false;
    }

    public function publish(): void
    {
        $this->validate([
            'postCaption' => ['required', 'string', 'max:300'],
            'platforms' => ['required', 'array', 'min:1'],
            'platforms.*' => [Rule::enum(Platform::class)],
            'postedAt' => ['required', 'date'],
        ]);

        if (! $this->idea->canPost()) {
            return;
        }

        Post::publish(
            $this->idea,
            trim($this->postCaption),
            array_map(Platform::from(...), $this->platforms),
            Carbon::parse($this->postedAt, config('planner.timezone')),
        );

        $this->idea->refresh();
        $this->fillNumbers();
    }

    public function recordStats(string $platform): void
    {
        $rules = collect(Stats::NUMBERS)->mapWithKeys(fn (string $field) => [
            "numbers.{$platform}.{$field}" => $field === 'average_watch_seconds'
                ? ['nullable', 'numeric', 'min:0', 'max:9999']
                : ['nullable', 'integer', 'min:0'],
        ]);
        $this->validate($rules->all());

        $values = $this->numbers[$platform] ?? [];

        if (! Stats::anyFilled($values)) {
            $this->addError("numbers.{$platform}", 'Fill in at least one number.');

            return;
        }

        $stats = $this->idea->post?->stats->first(fn (Stats $stats) => $stats->platform->value === $platform);

        if (! $stats) {
            return;
        }

        $stats->record(collect($values)->map(fn ($value) => $value === '' || $value === null ? null : $value + 0)->all());
        $this->idea->refresh();
    }

    public function deleteIdea()
    {
        $this->idea->delete();

        return $this->redirectRoute('board', navigate: true);
    }

    private function fillDetails(): void
    {
        $this->place = $this->idea->place;
        $this->caption = (string) $this->idea->caption;
        $this->notes = (string) $this->idea->notes;
        $this->seriesId = $this->idea->series_id;
    }

    private function fillNumbers(): void
    {
        foreach ($this->idea->post?->stats ?? [] as $stats) {
            $this->numbers[$stats->platform->value] = $stats->only(Stats::NUMBERS);
        }
    }

    public function render()
    {
        return view('livewire.show-idea', [
            'post' => $this->idea->post?->load('stats'),
            'allPlatforms' => Platform::cases(),
            'series' => $this->editing ? Series::orderBy('name')->get() : collect(),
        ])->title($this->idea->place);
    }
}
