<?php

namespace Backstage\Resources\ContentResource\Pages;

use BackedEnum;
use Backstage\Actions\Content\DuplicateContentAction;
use Backstage\Fields\Concerns\CanMapDynamicFields;
use Backstage\Models\Content;
use Backstage\Models\Language;
use Backstage\Models\Tag;
use Backstage\Resources\ContentResource;
use Backstage\Translations\Laravel\Facades\Translator;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\IconSize;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class EditContent extends EditRecord
{
    use CanMapDynamicFields;

    protected static string $resource = ContentResource::class;

    public function getBreadcrumbs(): array
    {
        $originalBreadcrumbs = parent::getBreadcrumbs();

        if (! $this->getRecord()->parent) {
            return $originalBreadcrumbs;
        }

        $breadcrumbs = [];

        $first = true;
        foreach ($originalBreadcrumbs as $key => $breadcrumb) {
            $breadcrumbs[$key] = $breadcrumb;

            if ($first) {
                $parents = $this->getRecord()->ancestors()->get()->reverse();
                foreach ($parents as $parent) {
                    $parentUrl = route('filament.backstage.resources.content.edit', [
                        'record' => $parent,
                        'tenant' => Filament::getTenant(),
                    ]);
                    $breadcrumbs[$parentUrl] = $parent->name;
                }
            }

            $first = false;
        }

        return $breadcrumbs;
    }

    protected function getHeaderActions(): array
    {
        $languages = Language::query()
            ->where('active', true)
            ->when($this->getRecord()->language_code ?? null, function ($query, $languageCode) {
                $query->where('languages.code', '!=', $languageCode);
            })
            ->get();

        $languageActions = $languages
            ->map(fn (Language $language) => Action::make($language->code)
                ->label($language->name)
                ->icon(fn () => 'data:image/svg+xml;base64,' . base64_encode(file_get_contents(flag_path(explode('-', $language->code)[0]))))
                ->modal(fn (Content $record) => ! $record->existingTranslation($language))
                ->modalIcon(fn () => Heroicon::OutlinedLanguage)
                ->modalHeading(fn () => __('Translate to ' . $language->name))
                ->modalDescription(fn () => __('This will create a new translation of the content in ' . $language->name))
                ->modalWidth(Width::Medium)
                ->modalAlignment(Alignment::Center)
                ->modalFooterActionsAlignment(Alignment::Center)
                ->modalSubmitActionLabel(__('Translate'))
                ->modalCancelActionLabel(__('Cancel'))
                ->action(function (Content $record) use ($language) {
                    $existingTranslation = $record->existingTranslation($language);

                    if ($existingTranslation) {
                        $url = self::getUrl([
                            'record' => $existingTranslation,
                        ], tenant: Filament::getTenant());

                        $this->redirect($url);

                        return;
                    }

                    $record->translate($language);

                    Notification::make()
                        ->title(__('Translating :title to :language', ['title' => $record->name, 'language' => $language->name]))
                        ->body(__('The content is being translated to ' . $language->name))
                        ->icon(fn () => Heroicon::OutlinedLanguage)
                        ->iconColor('success')
                        ->iconSize(IconSize::TwoExtraLarge)
                        ->send();
                }));

        if (! $languageActions->isEmpty()) {
            $all = $languageActions->all();

            $translateAllLanguage = Action::make('translate_all')
                ->label(__('Translate to all languages'))
                ->icon(fn () => Heroicon::OutlinedLanguage)
                ->requiresConfirmation()
                ->action(function (Content $record) use ($languages) {
                    foreach ($languages as $language) {
                        $record->translate(language: $language);
                    }
                });

            $languageActions = collect([$translateAllLanguage, ...$all]);
        }

        return [
            DuplicateContentAction::make('duplicate'),

            ActionGroup::make([
                ...$languageActions,
            ])
                ->button()
                ->color('gray')
                ->label(fn (): string => __('Translate'))
                ->icon(fn (): BackedEnum => Heroicon::OutlinedLanguage),

            Action::make('preview')
                ->icon(fn (): BackedEnum => Heroicon::OutlinedEye)
                ->url(fn () => $this->getRecord()->url)
                ->color('gray')
                ->openUrlInNewTab()
                ->disabled(fn () => ! $this->getRecord()->previewable())
                ->tooltip(fn () => $this->getRecord()->previewable() ? __('Preview content') : __('Content must be public, published and not expired to preview')),

            DeleteAction::make(),
        ];
    }

    public function translate($language)
    {
        $state = $this->form->getState();

        $values = collect($state['values'])->map(function ($value, $key) use ($language) {
            if (is_array($value)) {
                return collect($value)->map(function ($item) use ($language) {
                    if (is_array($item)) {
                        return collect($item)->map(function ($i) use ($language) {
                            if (isset($i['data'])) {
                                $i['data'] = collect($i['data'])->mapWithKeys(function ($text, $key) use ($language) {
                                    return [$key => Translator::translate($text, $language->code)];
                                })->toArray();
                            }

                            if (is_array($i)) {
                                return collect($i)->mapWithKeys(function ($value, $key) use ($language) {
                                    if (is_array($value) || is_null($value)) {
                                        return [$key => $value];
                                    }

                                    return [$key => Translator::translate($value, $language->code)];
                                })->toArray();
                            }

                            return $i;
                        })->toArray();
                    }

                    return $item;
                })->toArray();
            }

            if (is_null($value)) {
                return $value;
            }

            return Translator::translate($value, $language->code);
        })->toArray();

        $metaTags = collect($state['meta_tags'])->mapWithKeys(function ($value, $key) use ($language) {
            if (is_array($value) || is_null($value)) {
                return [$key => $value];
            }

            return [$key => Translator::translate($value, $language->code)];
        })->toArray();

        $state['values'] = $values;
        $state['meta_tags'] = $metaTags;
        $state['name'] = Translator::translate($state['name'], $language->code);

        $this->form->fill($state);

        Notification::make()
            ->title(__('Translated'))
            ->body(__('The content has been translated to ' . $language->name))
            ->send();
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        if (! isset($data[$this->getRecord()->valueColumn])) {
            $data[$this->getRecord()->valueColumn] = [];
        }

        // Get all values as an array: [ulid => value]
        $values = $this->getRecord()->getFormattedFieldValues();

        $this->getRecord()->values = $values;

        return $this->mutateBeforeFill($data);
    }

    protected function afterSave(): void
    {
        $this->handleTags();
        $this->handleValues();
        $this->updateEditedAt();
        $this->syncAuthors();
    }

    private function handleTags(): void
    {
        $tags = collect($this->data['tags'] ?? [])
            ->filter(fn ($tag) => filled($tag))
            ->map(fn (string $tag) => $this->record->tags()->updateOrCreate([
                'name' => $tag,
                'slug' => Str::slug($tag),
            ]))
            ->each(fn (Tag $tag) => $tag->sites()->syncWithoutDetaching($this->record->site));

        $this->record->tags()->sync($tags->pluck('ulid')->toArray());
    }

    private function handleValues(): void
    {
        collect($this->data['values'] ?? [])
            ->each(function ($value, $field) {
                $fieldModel = \Backstage\Fields\Models\Field::where('ulid', $field)->first();

                $value = $this->prepareValue($value);

                if ($this->shouldDeleteValue($value)) {
                    $this->deleteValue($field);

                    return;
                }

                if ($fieldModel && $fieldModel->field_type === 'builder') {
                    $this->handleBuilderField($value, $field);

                    return;
                }

                $this->updateOrCreateValue($value, $field);
            });
    }

    private function prepareValue($value)
    {
        return isset($value['value']) && is_array($value['value']) ? json_encode($value['value']) : $value;
    }

    private function shouldDeleteValue($value): bool
    {
        return blank($value);
    }

    private function deleteValue($field): void
    {
        $this->getRecord()->values()->where([
            'content_ulid' => $this->getRecord()->getKey(),
            'field_ulid' => $field,
        ])->delete();
    }

    private function handleBuilderField($value, $field): void
    {
        $value = $this->decodeAllJsonStrings($value);

        $this->getRecord()->values()->updateOrCreate([
            'content_ulid' => $this->getRecord()->getKey(),
            'field_ulid' => $field,
        ], [
            'value' => is_array($value) ? json_encode($value) : $value,
        ]);
    }

    private function updateOrCreateValue($value, $field): void
    {
        $this->getRecord()->values()->updateOrCreate([
            'content_ulid' => $this->getRecord()->getKey(),
            'field_ulid' => $field,
        ], [
            'value' => is_array($value) ? json_encode($value) : $value,
        ]);
    }

    private function updateEditedAt(): void
    {
        $this->getRecord()->update([
            'edited_at' => now(),
        ]);
    }

    private function syncAuthors(): void
    {
        $this->getRecord()->authors()->syncWithoutDetaching(Auth::id());
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = $this->mutateBeforeSave($data);

        $this->data['values'] = $data['values'] ?? [];

        unset($data['tags']);
        unset($data['values']);

        return $data;
    }

    private function decodeAllJsonStrings($data, $path = '')
    {
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                $currentPath = $path === '' ? $key : $path . '.' . $key;
                if (is_string($value)) {
                    $decoded = $value;
                    $decodeCount = 0;
                    while (is_string($decoded)) {
                        $json = json_decode($decoded, true);
                        if ($json !== null && (is_array($json) || is_object($json))) {
                            $decoded = $json;
                            $decodeCount++;
                        } else {
                            break;
                        }
                    }
                    if ($decodeCount > 0) {
                        $data[$key] = $this->decodeAllJsonStrings($decoded, $currentPath);
                    }
                } elseif (is_array($value)) {
                    $data[$key] = $this->decodeAllJsonStrings($value, $currentPath);
                }
            }
        }

        return $data;
    }
}
