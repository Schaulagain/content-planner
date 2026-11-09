<div>
    <div class="flex items-end justify-between pb-4">
        <h1 class="text-2xl font-semibold tracking-tight">Board</h1>
        <a href="{{ route('ideas.new') }}" wire:navigate class="press flex items-center gap-1 rounded-full bg-leaf px-3.5 py-2 text-sm font-medium text-white">
            <x-icon name="plus" class="size-4" />Idea
        </a>
    </div>

    <div class="space-y-5">
        @foreach ($stages as $stage)
            @php($items = $ideas[$stage->value] ?? collect())
            <section>
                <h2 class="flex items-center gap-1.5 px-1 pb-2 text-xs font-medium text-muted">
                    <x-icon :name="$stage->icon()" class="size-3.5" />{{ $stage->label() }}
                    <span class="tabular-nums">{{ $items->count() }}</span>
                </h2>

                @if ($items->isEmpty())
                    <p class="rounded-2xl border border-dashed border-line px-4 py-3 text-sm text-muted">Nothing here.</p>
                @else
                    <ul class="space-y-2">
                        @foreach ($items as $idea)
                            <li wire:key="idea-{{ $idea->id }}" class="flex items-center gap-3 rounded-2xl border border-line/70 bg-card p-2.5">
                                <a href="{{ route('ideas.show', $idea) }}" wire:navigate class="flex min-w-0 flex-1 items-center gap-3">
                                    @if ($idea->photoUrl())
                                        <img src="{{ $idea->photoUrl() }}" alt="" class="size-12 shrink-0 rounded-xl object-cover">
                                    @else
                                        <span class="grid size-12 shrink-0 place-items-center rounded-xl bg-band text-muted"><x-icon name="image" class="size-5" /></span>
                                    @endif
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-medium">{{ $idea->place }}@if ($idea->post?->series_number) <span class="text-muted">#{{ $idea->post->series_number }}</span>@endif</span>
                                        @if ($idea->caption)
                                            <span class="block truncate text-xs text-muted">{{ $idea->caption }}</span>
                                        @endif
                                    </span>
                                </a>
                                @if ($stage->previous())
                                    <button wire:click="back({{ $idea->id }})" class="press grid size-8 place-items-center rounded-full bg-page text-muted hover:text-ink" aria-label="Back to {{ $stage->previous()->label() }}">
                                        <x-icon name="chevron-left" class="size-4" />
                                    </button>
                                @endif
                                @if ($stage->next())
                                    <button wire:click="advance({{ $idea->id }})" class="press grid size-8 place-items-center rounded-full bg-page text-muted hover:text-ink" aria-label="Move to {{ $stage->next()->label() }}">
                                        <x-icon name="chevron-right" class="size-4" />
                                    </button>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
        @endforeach
    </div>
</div>
