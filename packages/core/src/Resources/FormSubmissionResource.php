<?php

namespace Backstage\Resources;

use Backstage\Models\FormSubmission;
use Backstage\Resources\FormSubmissionResource\Pages\ListFormSubmissions;
use Backstage\Resources\FormSubmissionResource\Pages\ViewFormSubmission;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

class FormSubmissionResource extends Resource
{
    protected static ?string $model = FormSubmission::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-inbox-arrow-down';

    public static function getModelLabel(): string
    {
        return __('Submission');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Submissions');
    }

    public static function getNavigationParentItem(): ?string
    {
        return __('Forms');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tab::make('Form')
                            ->schema([
                                TextInput::make('name')
                                    ->columnSpanFull()
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state) {
                                        $set('slug', Str::slug($state));
                                    }),
                                TextInput::make('slug')
                                    ->columnSpanFull()
                                    ->required()
                                    ->unique(ignoreRecord: true),
                                Textarea::make('notes')
                                    ->columnSpanFull()
                                    ->rows(3),
                            ]),
                    ]),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('form.name')
                    ->columnSpanFull()
                    ->label(__('Form'))
                    ->default('-'),
                TextEntry::make('notes')
                    ->columnSpanFull()
                    ->label(__('Notes'))
                    ->default('-'),
                TextEntry::make('content.name')
                    ->columnSpanFull()
                    ->label(__('Content'))
                    ->default('-')
                    ->state(function (FormSubmission $record): HtmlString {
                        return new HtmlString($record->content ? '<a href="' . $record->content->url . '" target="_blank">' . $record->content->name . '</a>' : '-');
                    }),
                TextEntry::make('language_code')
                    ->columnSpanFull()
                    ->label(__('Language'))
                    ->default('-'),
                TextEntry::make('country_code')
                    ->columnSpanFull()
                    ->label(__('Country'))
                    ->default('-'),
                TextEntry::make('ip_address')
                    ->columnSpanFull()
                    ->label(__('IP Address'))
                    ->default('-'),
                TextEntry::make('hostname')
                    ->columnSpanFull()
                    ->label(__('Hostname'))
                    ->default('-'),
                TextEntry::make('user_agent')
                    ->columnSpanFull()
                    ->label(__('User Agent'))
                    ->default('-'),
                TextEntry::make('submitted_at')
                    ->columnSpanFull()
                    ->label(__('Submitted At'))
                    ->default('-'),
                TextEntry::make('email_confirmed_at')
                    ->columnSpanFull()
                    ->label(__('Email confirmed At'))
                    ->default('-'),
                RepeatableEntry::make('values')
                    ->schema([
                        TextEntry::make('field.name')
                            ->state(function ($record) {
                                return $record->field?->name ?? '-';
                            }),
                        TextEntry::make('value'),

                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('form.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('content.name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('submitted_at')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('form_slug')
                    ->label('Form')
                    ->native(false)
                    ->searchable()
                    ->multiple()
                    ->preload()
                    ->relationship('form', 'name'),
            ])
            ->recordActions([
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFormSubmissions::route('/'),
            'view' => ViewFormSubmission::route('/{record}/view'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['values.field']);
    }
}
