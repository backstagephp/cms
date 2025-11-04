<?php

namespace Backstage\Resources\ContentResource\Pages;

use Backstage\Fields\Concerns\CanMapDynamicFields;
use Backstage\Models\Tag;
use Backstage\Resources\ContentResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Str;

class CreateContent extends CreateRecord
{
    protected static string $resource = ContentResource::class;

    use CanMapDynamicFields;

    protected static ?string $slug = 'content/create/{type}';

    public function getTitle(): string
    {
        return '';
    }

    public function mount(): void
    {
        $this->data['type_slug'] = request()->route()->parameter('type')->slug;

        $this->authorizeAccess();

        $this->fillForm();

        $this->previousUrl = url()->previous();
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['tags']);
        unset($data['values']);

        unset($data['media']);

        return $data;
    }

    protected function afterCreate(): void
    {
        collect($this->data['tags'] ?? [])
            ->filter(fn ($tag) => filled($tag))
            ->map(fn (string $tag) => $this->record->tags()->updateOrCreate([
                'name' => $tag,
                'slug' => Str::slug($tag),
            ]))
            ->each(fn (Tag $tag) => $tag->sites()->syncWithoutDetaching($this->record->site));

        collect($this->data['values'] ?? [])
            ->filter(fn (string | array | null $value) => filled($value))
            ->each(fn (string | array $value, $field) => $this->record->values()->create([
                'field_ulid' => $field,
                'value' => is_array($value) ? json_encode($value) : $value,
            ]));

        $this->getRecord()->update([
            'creator_id' => auth()->id(),
            'edited_at' => now(),
        ]);

        $this->getRecord()->authors()->attach(auth()->id());
    }
}
