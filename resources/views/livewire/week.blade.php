<div>
    <h1 class="text-2xl font-semibold tracking-tight">This week</h1>
    <p class="pt-1 text-sm text-muted">{{ $week->start->format('j M') }} – {{ $week->end->format('j M') }} · <span class="tabular-nums">{{ $posts->count() }} of {{ $target }}</span> posts</p>

    <div class="mt-3 flex gap-1.5" aria-hidden="true">
        @for ($i = 0; $i < max($target, $posts->count()); $i++)
            <span @class(['h-1.5 flex-1 rounded-full', 'bg-leaf' => $i < $posts->count(), 'bg-band' => $i >= $posts->count()])></span>
        @endfor
    </div>

    <ul class="mt-5 space-y-2">
        @forelse ($posts as $post)
            <li wire:key="post-{{ $post->id }}">
                <a href="{{ route('ideas.show', $post->idea) }}" wire:navigate class="block rounded-2xl border border-line/70 bg-card p-3">
                    <div class="flex items-baseline justify-between gap-2">
                        <span class="truncate text-sm font-medium">{{ $post->idea->heading() }}</span>
                        <span class="shrink-0 text-xs text-muted">{{ $post->postedAtLocal()->format('D g:i a') }}</span>
                    </div>
                    <div class="mt-2 flex flex-wrap gap-x-4 gap-y-1 text-xs text-muted">
                        @foreach ($post->stats as $stats)
                            <span>
                                <span class="font-medium text-ink">{{ $stats->platform->label() }}</span>
                                @if ($stats->recorded_at)
                                    {{ number_format($stats->views ?? 0) }} views · {{ $stats->shares ?? 0 }} shares · {{ $stats->saves ?? 0 }} saves
                                @else
                                    no numbers yet
                                @endif
                            </span>
                        @endforeach
                    </div>
                </a>
            </li>
        @empty
            <li class="rounded-2xl border border-dashed border-line px-4 py-3 text-sm text-muted">Nothing posted yet this week.</li>
        @endforelse
    </ul>

    <form wire:submit="save" class="mt-6 rounded-3xl border border-line/70 bg-card p-4">
        <label for="learning" class="text-sm font-medium">What did this week teach you?</label>
        <textarea id="learning" wire:model="learning" rows="2" placeholder="One line is enough"
                  class="mt-2 w-full rounded-2xl border border-line/70 bg-page px-3 py-2.5 text-base outline-none focus:border-ink/40"></textarea>
        @error('learning') <p class="text-xs text-bad">{{ $message }}</p> @enderror
        <button class="press mt-2 grid h-10 w-full place-items-center rounded-2xl bg-accent text-sm font-medium text-page">
            <span wire:loading.remove wire:target="save">{{ $saved ? 'Saved' : 'Save' }}</span>
            <x-spinner wire:loading wire:target="save" class="size-4" />
        </button>
    </form>

    @if ($learnings->isNotEmpty())
        <section class="mt-6">
            <h2 class="px-1 pb-2 text-xs font-medium text-muted">Earlier weeks</h2>
            <ul class="space-y-2">
                @foreach ($learnings as $review)
                    <li class="rounded-2xl bg-band/60 px-4 py-2.5 text-sm">
                        <span class="block text-[11px] text-muted">Week of {{ $review->week_start->format('j M') }}</span>
                        {{ $review->learning }}
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</div>
