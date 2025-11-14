@php
    $instance = new $scope();

    $margins = match (true) {
        $instance instanceof \Filament\Pages\SimplePage => 'mb-6',
        $instance instanceof \Filament\Pages\Page => 'mt-5 -mb-4',
        default => null,
    };

@endphp

<div class="{{ $margins }}">
    <div class="pointer-events-auto flex items-center justify-between gap-x-6  px-6 py-2.5 sm:rounded-xl sm:py-3 sm:pl-4 sm:pr-3.5 dark:ring-1 dark:ring-inset dark:ring-white/10"
        style="{{ $this->getColor() }};background-color: var(--color-600);">
        <p class="text-sm/6 text-white">
            <strong class="font-semibold">
                {{ $announcement->title }}
            </strong>

            <svg viewBox="0 0 2 2" aria-hidden="true" class="mx-2 inline size-0.5 fill-current">
                <circle r="1" cx="1" cy="1" />
            </svg>{{ $announcement->content }}
        </p>
        @if ($this->canMarkAsRead())
            <button type="button" class="-m-3 flex-none p-3 focus-visible:-outline-offset-4" wire:click="markAsRead"
                aria-label="Dismiss announcement">
                <span class="sr-only">Dismiss</span>
                <svg viewBox="0 0 20 20" fill="currentColor" data-slot="icon" aria-hidden="true"
                    class="size-5 text-white">
                    <path
                        d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                </svg>
            </button>
        @endif
    </div>
</div>
