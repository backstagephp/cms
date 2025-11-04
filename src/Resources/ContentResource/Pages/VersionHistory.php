<?php

namespace Backstage\Resources\ContentResource\Pages;

use Backstage\Fields\Models\Field;
use Backstage\Models\ContentFieldValue;
use Backstage\Models\Version;
use Backstage\Resources\ContentResource;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Infolists;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

class VersionHistory extends ManageRelatedRecords
{
    protected static string $resource = ContentResource::class;

    protected static string $relationship = 'versions';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-document-duplicate';

    public function getTitle(): string | Htmlable
    {
        $recordTitle = $this->getRecordTitle();

        $recordTitle = $recordTitle instanceof Htmlable ? $recordTitle->toHtml() : $recordTitle;

        return __('Revisions: :resource', [
            'resource' => $recordTitle,
        ]);
    }

    public function getBreadcrumb(): string
    {
        return __('Revisions');
    }

    public static function getNavigationLabel(): string
    {
        return __('Revisions');
    }

    public function getModelLabel(): string
    {
        return __('Revision');
    }

    public static function getPluralModelLabel(): string
    {
        return __('Revisions');
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('Date'))
                    ->sortable()
                    ->dateTime(),
                Tables\Columns\TextColumn::make('data.meta_tags.title')
                    ->label(__('Name'))
                    ->searchable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->label(__('View'))
                    ->modalHeading(fn (Version $record) => __('Version from :date', ['date' => $record->created_at->format('Y-m-d H:i:s')]))
                    ->schema(function (Version $record) {
                        $entries = [];
                        $fields = Field::where('model_type', 'type')
                            ->where('model_key', $record->content->type->slug)->get();
                        $entries[] = Infolists\Components\TextEntry::make('meta_tags')
                            ->label(__('Meta tags'))
                            ->formatState(function () use ($record) {
                                $original = collect($record->content->meta_tags)->map(function ($value, $key) {
                                    return (string) new HtmlString($key . ': ' . (is_string($value) || is_null($value) ? $value : json_encode($value)));
                                })
                                    ->flatten()->implode('<br>');
                                $new = collect($record->data['meta_tags'])->map(function ($value, $key) {
                                    return (string) new HtmlString($key . ': ' . (is_string($value) || is_null($value) ? $value : json_encode($value)));
                                })->flatten()->implode('<br>');

                                return view('backstage::filament.utility.diff', [
                                    'original' => $original,
                                    'new' => $new,
                                    'name' => __('Meta tags'),
                                ]);
                            });
                        foreach ($fields as $field) {
                            $originalValue = $record->content->rawField($field->slug);
                            $newValue = $record->data['fields'][$field->ulid] ?? '';
                            $newValue = is_array($newValue) ? json_encode($newValue) : $newValue;
                            $entries[] = Infolists\Components\TextEntry::make($field->ulid)
                                ->label($field->name)
                                ->formatState(function () use ($originalValue, $newValue, $field) {
                                    switch ($field->field_type) {
                                        case 'rich-editor':
                                            $originalValue = ContentFieldValue::getRichEditorHtml($originalValue);
                                            $newValue = ContentFieldValue::getRichEditorHtml($newValue);

                                            break;
                                        default:
                                            if ($field->hasRelation()) {
                                                $originalValue = ContentFieldValue::getContentRelation($originalValue)?->pluck('name')->implode(', ');
                                                $newValue = ContentFieldValue::getContentRelation($newValue)?->pluck('name')->implode(', ');
                                            }
                                            $originalValue = $originalValue;
                                            $newValue = $newValue;

                                            break;
                                    }

                                    return view('backstage::filament.utility.diff', [
                                        'original' => $originalValue,
                                        'new' => $newValue,
                                        'name' => $field->name,
                                    ]);
                                });
                        }

                        return $entries;
                    })
                    ->modalFooterActions([
                        Action::make('restore')
                            ->label(__('Restore revision'))
                            ->icon('heroicon-o-arrow-path')
                            ->action(function (Version $record, Action $action): void {
                                $record->content->update([
                                    'meta_tags' => $record->data['meta_tags'] ?? [],
                                ]);
                                $record->content->values()->delete();
                                foreach ($record->content->type->fields as $field) {
                                    if (! isset($record->data['fields'][$field->ulid])) {
                                        continue;
                                    }
                                    $value = $record->data['fields'][$field->ulid];
                                    $record->content->values()->create([
                                        'field_ulid' => $field->ulid,
                                        'value' => json_encode($value),
                                    ]);
                                }

                                Notification::make()
                                    ->success()
                                    ->title(__('Version restored'))
                                    ->send();

                                $this->redirect($this->getResource()::getUrl('edit', ['record' => $record->content]));
                            }),
                        Action::make('cancel')
                            ->label(__('Cancel'))
                            ->close()
                            ->color('gray'),
                    ])
                    ->modalFooterActionsAlignment(Alignment::End),
            ]);
    }
}
