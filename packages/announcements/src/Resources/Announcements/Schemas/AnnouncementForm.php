<?php

namespace Backstage\Announcements\Resources\Announcements\Schemas;

use Backstage\Announcements\AnnouncementsPlugin;
use Backstage\Announcements\Collections\ScopeCollection;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\FusedGroup;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Schema;
use Filament\Support\Colors\ColorManager;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->required(),

                        Textarea::make('content')
                            ->required()
                            ->columnSpanFull(),

                        Select::make('scopes')
                            ->searchable()
                            ->multiple()
                            ->options(function ($state) {
                                $plugin = AnnouncementsPlugin::get();
                                $forcedScopes = $plugin->getForcedScopes();

                                return ScopeCollection::create(Filament::getCurrentPanel(), $forcedScopes)->toArray();
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
                                            $key => (string) Text::make(ucfirst($key))->color($key)->container($select->getContainer())->toHtmlString(),
                                        ];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->getOptionLabelsUsing(fn ($value) => ucfirst($value))
                            ->native(false)
                            ->allowHtml()
                            ->required(),

                        FusedGroup::make()
                            ->schema([
                                DateTimePicker::make('start_date')
                                    ->native(false)
                                    ->seconds(false)
                                    ->placeholder(__('Start Date')),

                                DateTimePicker::make('end_date')
                                    ->native(false)
                                    ->seconds(false)
                                    ->placeholder(__('End Date'))
                                    ->registerActions([
                                        Action::make('information')
                                            ->label(__('Information'))
                                            ->icon(Heroicon::OutlinedInformationCircle)
                                            ->color('gray')
                                            ->modal()
                                            ->modalIcon(Heroicon::OutlinedInformationCircle)
                                            ->modalIconColor('info')
                                            ->modalHeading(__('Information'))
                                            ->modalContent(Html::make(__('If setting a start date, the announcement will only be displayed after the start date. If setting an end date, the announcement will only be displayed before the end date. If setting both, the announcement will only be displayed between the start and end dates.')))
                                            ->modalFooterActions([])
                                            ->modalWidth(Width::Large),
                                    ]),
                            ])
                            ->columns(2)
                            ->beforeLabel(fn () => __('Start date'))
                            ->afterLabel(function (FusedGroup $component): Htmlable {
                                $target = $component->getChildComponents()[1]->getActions()['information']->getLivewireClickHandler();

                                $link = view('backstage/announcements::components.after-label', [
                                    'target' => $target,
                                    'label' => __('End date'),
                                ])->render();

                                return new HtmlString($link);
                            }),
                    ]),
            ]);
    }
}
