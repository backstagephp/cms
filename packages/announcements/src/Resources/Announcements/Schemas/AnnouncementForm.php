<?php

namespace Backstage\Announcements\Resources\Announcements\Schemas;

use Backstage\Announcements\AnnouncementsPlugin;
use Backstage\Announcements\Collections\ScopeCollection;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Colors\ColorManager;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\HtmlString;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),

                Textarea::make('content')
                    ->required()
                    ->columnSpanFull(),

                Select::make('scopes')
                    ->searchable()
                    ->multiple()
                    ->options(function () {
                        $plugin = AnnouncementsPlugin::get();
                        $forcedScopes = $plugin->getForcedScopes();

                        return ScopeCollection::create(Filament::getCurrentPanel(), $forcedScopes)->toArray();
                    })
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) {
                            return $state;
                        }

                        // Convert class names to formatted names for display
                        $plugin = AnnouncementsPlugin::get();
                        $forcedScopes = $plugin->getForcedScopes();
                        $allScopes = ScopeCollection::create(Filament::getCurrentPanel(), $forcedScopes)->toArray();

                        return array_map(fn ($value) => $allScopes[$value] ?? $value, (array) $state);
                    })
                    ->required(),

                Select::make('color')
                    ->live()
                    ->prefixIcon(Heroicon::OutlinedCube, true)
                    ->prefixIconColor(fn (?string $state) => $state ?? 'gray')
                    ->options(function (Select $select) {
                        $colors = ColorManager::DEFAULT_COLORS;

                        return collect($colors)
                            ->keys()
                            ->mapWithKeys(function ($key) use ($select) {
                                return [
                                    $key => (string) Text::make(ucfirst($key))->color($key)->container($select->getContainer())->toHtmlString()
                                ];
                            })
                            ->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->getOptionLabelsUsing(fn($value) => ucfirst($value))
                    ->native(false)
                    ->allowHtml()
                    ->required(),
            ]);
    }
}
