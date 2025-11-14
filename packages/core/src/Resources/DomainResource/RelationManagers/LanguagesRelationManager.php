<?php

namespace Backstage\Resources\DomainResource\RelationManagers;

use Backstage\Models\Domain;
use Backstage\Models\Language;
use Filament\Actions\AttachAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DetachAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Livewire\Component;

class LanguagesRelationManager extends RelationManager
{
    protected static string $relationship = 'languages';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('Languages');
    }

    public static function getModelLabel(): string
    {
        return __('Language');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Languages');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make()
                    ->columnSpanFull()
                    ->schema([
                        Section::make(__('Language'))
                            ->columnSpanFull()
                            ->schema([
                                Select::make('language_code')
                                    ->label(__('Language'))
                                    ->columnSpanFull()
                                    ->placeholder(__('Select Language'))
                                    ->options(
                                        Language::withoutGlobalScopes()
                                            ->where('active', 1)
                                            ->get()
                                            ->sort()
                                            ->groupBy(function ($language) {
                                                return Str::contains($language->code, '-') ? localized_country_name($language->code) : __('Worldwide');
                                            })
                                            ->mapWithKeys(fn ($languages, $countryName) => [
                                                $countryName => $languages->mapWithKeys(fn ($language) => [
                                                    $language->code => '<img src="data:image/svg+xml;base64,' . base64_encode(file_get_contents(flag_path(explode('-', $language->code)[0]))) . '" class="inline-block relative w-5" style="top: -1px; margin-right: 3px;"> ' . localized_language_name($language->code) . ' (' . $countryName . ')',
                                                ])->toArray(),
                                            ])
                                    )
                                    ->allowHtml(),
                                TextInput::make('path')
                                    ->label(__('Path'))
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('language')
            ->columns([
                ImageColumn::make('language_code')
                    ->label('Language')
                    ->getStateUsing(fn (Language $record) => explode('-', $record->language_code)[0])
                    ->view('backstage::filament.tables.columns.language-flag-column'),
                ViewColumn::make('country_code')
                    ->label('Country')
                    ->getStateUsing(fn (Language $record) => explode('-', $record->language_code)[1] ?? 'worldwide')
                    ->view('backstage::filament.tables.columns.country-flag-column'),
                TextColumn::make('path')
                    ->label(__('Path'))
                    ->searchable()
                    ->limit(),
            ])
            ->filters([])
            ->headerActions([
                AttachAction::make()
                    ->preloadRecordSelect()
                    ->recordSelectSearchColumns(['name', 'code', 'native'])
                    ->recordTitleAttribute('native')
                    ->recordSelectOptionsQuery(fn (Builder $query) => $query->withoutGlobalScopes())
                    ->schema(fn (AttachAction $action): array => [
                        $action->getRecordSelect(),
                        TextInput::make('path'),
                    ])
                    ->action(function (array $arguments, array $data, AttachAction $action, Table $table) {
                        $recordId = $data['recordId'];
                        $path = $data['path'];

                        /**
                         * @var Domain $language
                         */
                        $ownerRecord = $this->getOwnerRecord();

                        $attachment = $ownerRecord->languages()->attach($recordId, [
                            'path' => $path,
                        ]);

                        if (! $attachment) {
                            $action->failureNotificationTitle(__('Failed to attach language'));

                            $action->failure();

                            return;
                        }

                        $action->success();
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->after(function (Component $livewire) {
                        $livewire->dispatch('refreshFields');
                    }),
                DetachAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
