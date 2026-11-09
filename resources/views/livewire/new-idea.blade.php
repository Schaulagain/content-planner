<div>
    <h1 class="pb-4 text-2xl font-semibold tracking-tight">New idea</h1>

    <form wire:submit="save" class="space-y-3" x-data="ideaForm($wire)">
        <label class="block">
            <span class="sr-only">Photo</span>
            <span class="press relative grid aspect-[4/3] w-full cursor-pointer place-items-center overflow-hidden rounded-3xl border border-dashed border-line bg-card text-muted">
                <template x-if="preview"><img :src="preview" alt="" class="absolute inset-0 size-full object-cover"></template>
                <span x-show="! preview" class="flex flex-col items-center gap-1 text-sm"><x-icon name="camera" class="size-6" />Add a photo of the spot</span>
                <span x-show="shrinking" class="absolute inset-0 grid place-items-center bg-card/70"><x-spinner class="size-5" /></span>
            </span>
            <input type="file" accept="image/*" class="sr-only" x-on:change="pick($event)">
        </label>
        @error('photo') <p class="px-1 text-xs text-bad">{{ $message }}</p> @enderror

        <div class="flex gap-2">
            <input type="text" wire:model="place" placeholder="Place" aria-label="Place" autocomplete="off"
                   class="min-w-0 flex-1 rounded-2xl border border-line/70 bg-card px-4 py-3 text-base outline-none focus:border-ink/40">
            <button type="button" x-on:click="locate()" class="press grid size-[50px] shrink-0 place-items-center rounded-2xl border border-line/70 bg-card text-muted hover:text-ink" aria-label="Use where I am">
                <x-icon name="map-pin" class="size-5" x-show="! locating" />
                <x-spinner class="size-5" x-show="locating" x-cloak />
            </button>
        </div>
        <p x-show="locationNote" x-text="locationNote" class="px-1 text-xs text-muted" x-cloak></p>
        @error('place') <p class="px-1 text-xs text-bad">{{ $message }}</p> @enderror

        <textarea wire:model="caption" rows="2" placeholder="A thought about this place (the caption)" aria-label="Caption"
                  class="w-full rounded-2xl border border-line/70 bg-card px-4 py-3 text-base outline-none focus:border-ink/40"></textarea>

        <textarea wire:model="notes" rows="2" placeholder="Notes: light, timing, shots to get" aria-label="Notes"
                  class="w-full rounded-2xl border border-line/70 bg-card px-4 py-3 text-base outline-none focus:border-ink/40"></textarea>

        <div class="rounded-2xl border border-line/70 bg-card px-4 py-3">
            <label for="series" class="text-xs text-muted">Series</label>
            <select id="series" wire:model="seriesId" class="mt-1 w-full bg-transparent text-base outline-none">
                <option value="">None</option>
                @foreach ($series as $one)
                    <option value="{{ $one->id }}">{{ $one->name }}</option>
                @endforeach
            </select>
            <input type="text" wire:model="newSeries" placeholder="or start a new one" aria-label="New series"
                   class="mt-2 w-full border-t border-line/60 bg-transparent pt-2 text-sm outline-none">
        </div>

        <button class="press grid h-12 w-full place-items-center rounded-2xl bg-accent font-medium text-page disabled:opacity-70" wire:loading.attr="disabled" x-bind:disabled="shrinking">
            <span wire:loading.remove wire:target="save,photo">Save idea</span>
            <x-spinner wire:loading wire:target="save,photo" class="size-5" />
        </button>
    </form>
</div>
