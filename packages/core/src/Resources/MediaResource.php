<?php

namespace Backstage\Resources;

use Backstage\Media\Resources\MediaResource as Resource;
use Backstage\Models\Media;
use Backstage\Translations\Laravel\Facades\Translator;
use Backstage\Translations\Laravel\Models\Language;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\ImageEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class MediaResource extends Resource
{
    private static ?array $cachedLanguages = null;

    private static function getLanguages(): array
    {
        if (self::$cachedLanguages === null) {
            $languages = Language::all();

            $default = $languages->first(fn ($lang) => $lang->default == true);
            $others = $languages->filter(fn ($lang) => $lang->default != true)->values();

            self::$cachedLanguages = [
                'default' => $default,
                'others' => $others,
                'by_code' => $languages->keyBy('code'),
            ];
        }

        return self::$cachedLanguages;
    }

    public static function table(Table $table): Table
    {
        $altTextFormSchema = self::getAltTextFormSchema();

        return parent::table($table)
            ->headerActions([
                Action::make('upload')
                    ->modalHeading(__('Upload media'))
                    ->slideOver()
                    ->schema([
                        FileUpload::make('media')
                            ->label(__('Media'))
                            ->disk('uploadcare')
                            ->multiple(),
                    ])
                    ->action(function (array $data) {
                        dd($data);
                        // foreach ($data['media'] as $file) {
                        //     $media = Media::create([
                        //         'url' => $media['url'],
                        //         'alt_text' => $media['alt_text'],
                        //     ]);
                        // }
                    }),
            ])
            ->recordActions([
                ...parent::table($table)->getRecordActions(),
                Action::make('alt-text')
                    ->modalHeading(__('Manage alt text for this media'))
                    ->hiddenLabel()
                    ->icon('heroicon-o-tag')
                    ->tooltip(__('Manage alt text'))
                    ->slideOver()
                    ->fillForm(fn (Media | Model $record) => self::getAltTextFormData($record))
                    ->action(fn (array $data, Media | Model $record) => self::saveAltText($data, $record))
                    ->schema([
                        // ImageEntry::make('url')
                        //     ->label(__('Media'))
                        //     ->formatStateUsing(fn ($state) => $state ? url($state) : null)
                        //     ->height(200),
                        Grid::make(2)
                            ->schema([
                                ...$altTextFormSchema,
                            ]),
                    ]),
            ]);
    }

    private static function getAltTextFormSchema(): array
    {
        $schema = [];

        $languages = self::getLanguages();

        // Add default language first
        if ($languages['default']) {
            $schema[] =
                Grid::make(2)
                    ->schema([
                        TextInput::make('alt')
                            ->label(__('Alt Text'))
                            ->prefixIcon(country_flag($languages['default']->code), true)
                            ->helperText(__('The alt text for the media in the default language. We can automatically translate this to other languages using AI.'))
                            ->required()
                            ->columnSpanFull(),
                    ])->columnSpanFull();
        }

        // Then add other languages
        foreach ($languages['others'] as $language) {
            $schema[] = TextInput::make('alt_text_' . $language->code)
                ->label(__('Alt Text'))
                ->suffixActions([
                    Action::make('translate_from_default')
                        ->icon(Heroicon::OutlinedLanguage)
                        ->tooltip(__('Translate from default language'))
                        ->action(function (Get $get, Set $set) use ($language) {
                            $defaultAlt = $get('alt');

                            $translator = Translator::translate($defaultAlt, $language->code);

                            $set('alt_text_' . $language->code, $translator);
                        }),
                ], true)
                ->prefixIcon(country_flag($language->code), true);
        }

        return $schema;
    }

    private static function getAltTextFormData(Media | Model $record): array
    {
        $languages = self::getLanguages();

        $data = [
            'alt' => $record->getTranslatedAttribute('alt', $languages['default']->code) ?? '',
        ];

        foreach ($languages['others'] as $language) {
            $data['alt_text_' . $language->code] = $record->getTranslatedAttribute('alt', $language->code) ?? '';
        }

        return $data;
    }

    private static function saveAltText(array $data, Media | Model $record): void
    {
        $languages = self::getLanguages();

        $record->updateQuietly([
            'alt' => $data['alt'],
        ]);

        $record->pushTranslateAttribute('alt', $data['alt'], $languages['default']->code);

        foreach ($languages['others'] as $language) {
            $key = 'alt_text_' . $language->code;
            if (isset($data[$key])) {
                $record->pushTranslateAttribute('alt', $data[$key], $language->code);
            }
        }

        Notification::make()
            ->title(__('Alt text updated'))
            ->body(__('The alt text has been updated for the media.'))
            ->send();

    }
}
