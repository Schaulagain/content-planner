<div>
    <a href="{{ route('board') }}" wire:navigate class="press inline-flex items-center gap-1 pb-3 text-sm text-muted hover:text-ink">
        <x-icon name="chevron-left" class="size-4" />Board
    </a>

    @if ($idea->photoUrl())
        <img src="{{ $idea->photoUrl() }}" alt="" class="mb-4 aspect-[4/3] w-full rounded-3xl object-cover">
    @endif

    @if ($editing)
        <form wire:submit="saveDetails" class="space-y-3">
            <input type="text" wire:model="place" aria-label="Place"
                   class="w-full rounded-2xl border border-line/70 bg-card px-4 py-3 text-base outline-none focus:border-ink/40">
            @error('place') <p class="px-1 text-xs text-bad">{{ $message }}</p> @enderror
            <textarea wire:model="caption" rows="2" aria-label="Caption" placeholder="Caption"
                      class="w-full rounded-2xl border border-line/70 bg-card px-4 py-3 text-base outline-none focus:border-ink/40"></textarea>
            <textarea wire:model="notes" rows="3" aria-label="Notes" placeholder="Notes"
                      class="w-full rounded-2xl border border-line/70 bg-card px-4 py-3 text-base outline-none focus:border-ink/40"></textarea>
            <select wire:model="seriesId" aria-label="Series" class="w-full rounded-2xl border border-line/70 bg-card px-4 py-3 text-base outline-none">
                <option value="">No series</option>
                @foreach ($series as $one)
                    <option value="{{ $one->id }}">{{ $one->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button type="button" wire:click="cancelEditing" class="press h-11 flex-1 rounded-2xl bg-band text-sm font-medium">Cancel</button>
                <button class="press h-11 flex-1 rounded-2xl bg-accent text-sm font-medium text-page">Save</button>
            </div>
        </form>
    @else
        <div class="flex items-center justify-between">
            <p class="flex items-center gap-1.5 text-xs font-medium text-muted"><x-icon :name="$idea->stage->icon()" class="size-3.5" />{{ $idea->stage->label() }}</p>
            <button wire:click="$set('editing', true)" class="press text-xs font-medium text-muted hover:text-ink">Edit</button>
        </div>
        <h1 class="pt-1 text-2xl font-semibold tracking-tight">{{ $idea->heading() }}</h1>
        @if ($idea->caption)
            <p class="pt-2 text-base">{{ $idea->caption }}</p>
        @endif
        @if ($idea->notes)
            <p class="whitespace-pre-line pt-2 text-sm text-muted">{{ $idea->notes }}</p>
        @endif
        @if ($idea->latitude !== null)
            <a href="https://www.openstreetmap.org/?mlat={{ $idea->latitude }}&mlon={{ $idea->longitude }}#map=15/{{ $idea->latitude }}/{{ $idea->longitude }}" target="_blank" rel="noopener"
               class="mt-2 inline-flex items-center gap-1 text-xs text-muted hover:text-ink"><x-icon name="map-pin" class="size-3.5" />On the map</a>
        @endif
    @endif

    @if ($idea->canPost())
        <form wire:submit="publish" class="mt-6 space-y-3 rounded-3xl border border-line/70 bg-card p-4">
            <h2 class="flex items-center gap-1.5 text-sm font-medium"><x-icon name="send" class="size-4" />Mark as posted</h2>
            <textarea wire:model="postCaption" rows="2" aria-label="Caption used" placeholder="The caption you used"
                      class="w-full rounded-2xl border border-line/70 bg-page px-3 py-2.5 text-base outline-none focus:border-ink/40"></textarea>
            @error('postCaption') <p class="text-xs text-bad">{{ $message }}</p> @enderror
            <div class="flex flex-wrap gap-2">
                @foreach ($allPlatforms as $platform)
                    <label class="press flex cursor-pointer items-center gap-2 rounded-full border border-line/70 px-3 py-1.5 text-sm has-[:checked]:border-leaf has-[:checked]:bg-leaf/10">
                        <input type="checkbox" wire:model="platforms" value="{{ $platform->value }}" class="accent-[var(--leaf)]">{{ $platform->label() }}
                    </label>
                @endforeach
            </div>
            @error('platforms') <p class="text-xs text-bad">Pick at least one platform.</p> @enderror
            <input type="datetime-local" wire:model="postedAt" aria-label="Posted at"
                   class="w-full rounded-2xl border border-line/70 bg-page px-3 py-2.5 text-base outline-none">
            <button class="press grid h-11 w-full place-items-center rounded-2xl bg-accent text-sm font-medium text-page disabled:opacity-70" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="publish">It's up</span>
                <x-spinner wire:loading wire:target="publish" class="size-5" />
            </button>
        </form>
    @endif

    @if ($post)
        <section class="mt-6">
            <h2 class="px-1 text-sm font-medium">Posted {{ $post->postedAtLocal()->format('D j M, g:i a') }}</h2>
            <p class="px-1 pt-1 text-sm text-muted">“{{ $post->caption }}”</p>
            @unless ($post->statsAreDue() || $post->hasAllStats())
                <p class="flex items-center gap-1.5 px-1 pt-2 text-xs text-muted"><x-icon name="clock" class="size-3.5" />Stats are worth writing down after {{ config('planner.stats_after_hours') }} hours.</p>
            @endunless

            <div class="mt-3 space-y-3">
                @foreach ($post->stats as $stats)
                    @php($key = $stats->platform->value)
                    <form wire:submit="recordStats('{{ $key }}')" wire:key="stats-{{ $key }}" class="rounded-3xl border border-line/70 bg-card p-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-sm font-medium">{{ $stats->platform->label() }}</h3>
                            @if ($stats->recorded_at)
                                <span class="flex items-center gap-1 text-xs text-leaf"><x-icon name="check" class="size-3.5" />Saved</span>
                            @endif
                        </div>
                        <div class="mt-3 grid grid-cols-3 gap-2">
                            @foreach (['views' => 'Views', 'likes' => 'Likes', 'comments' => 'Comments', 'shares' => 'Shares', 'saves' => 'Saves', 'average_watch_seconds' => 'Avg watch (s)'] as $field => $label)
                                <label class="rounded-xl bg-page px-2.5 py-1.5">
                                    <span class="block text-[11px] text-muted">{{ $label }}</span>
                                    <input type="number" inputmode="decimal" step="{{ $field === 'average_watch_seconds' ? '0.01' : '1' }}" min="0"
                                           wire:model="numbers.{{ $key }}.{{ $field }}" class="w-full bg-transparent text-base font-medium tabular-nums outline-none">
                                </label>
                            @endforeach
                        </div>
                        @error("numbers.$key") <p class="pt-2 text-xs text-bad">{{ $message }}</p> @enderror
                        @error("numbers.$key.*") <p class="pt-2 text-xs text-bad">{{ $message }}</p> @enderror
                        <button class="press mt-3 grid h-10 w-full place-items-center rounded-2xl bg-band text-sm font-medium disabled:opacity-70" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="recordStats('{{ $key }}')">Save numbers</span>
                            <x-spinner wire:loading wire:target="recordStats('{{ $key }}')" class="size-4" />
                        </button>
                    </form>
                @endforeach
            </div>
        </section>
    @endif

    <button wire:click="deleteIdea" wire:confirm="Delete this idea{{ $post ? ' and its stats' : '' }}?"
            class="press mt-8 inline-flex items-center gap-1.5 px-1 text-xs text-muted hover:text-bad">
        <x-icon name="trash" class="size-3.5" />Delete
    </button>
</div>
