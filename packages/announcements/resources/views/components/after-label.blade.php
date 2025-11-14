@use(Filament\Schemas\Components\Icon)
@use(Filament\Support\Icons\Heroicon)

<div class="flex items-center gap-1">
    <span>
        {{ $label }}
    </span>

    <span wire:click="{{ $target }}" class="cursor-pointer">
        {{ Icon::make(Heroicon::OutlinedInformationCircle)->color('info')->toHtmlString() }}
    </span>
</div>
