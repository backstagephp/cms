<?php

namespace Backstage\Actions\Content;

use Filament\Actions\ReplicateAction;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class DuplicateContentAction extends ReplicateAction
{
    protected function getNextAvailableName(Model $model, string $field, string $value): string
    {
        $baseName = preg_replace('/-\d+$/', '', $value);
        $copyNumber = 2;

        while ($model->where($field, $baseName . '-' . $copyNumber)->exists()) {
            $copyNumber++;
        }

        return $baseName . '-' . $copyNumber;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Duplicate')
            ->icon('heroicon-o-document-duplicate')
            ->color('gray')
            ->beforeReplicaSaved(function (Model $replica): void {
                $replica->edited_at = now();

                if (isset($replica->path)) {
                    $replica->path = $this->getNextAvailableName($replica, 'path', $replica->path);
                }

                if (isset($replica->slug)) {
                    $replica->slug = $this->getNextAvailableName($replica, 'slug', $replica->slug);
                }
            })
            ->after(function (Model $replica): void {
                $replica->tags()->sync($this->getRecord()->tags->pluck('ulid')->toArray());

                $this->getRecord()->values->each(fn ($value) => $replica->values()->updateOrCreate([
                    'content_ulid' => $replica->getKey(),
                    'field_ulid' => $value->field_ulid,
                ], [
                    'value' => $value->value,
                ]));
            })
            // ->modalHeading(function () {
            //     return "Duplicate {$this->getRecord()->name} {$this->getRecord()->type->name}";
            // })
            ->modalHeading("Duplicate {$this->getRecord()?->name} {$this->getRecord()?->type?->name}")
            ->requiresConfirmation()
            ->successNotification(
                Notification::make()
                    ->success()
                    ->title('Content duplicated')
                    ->body(fn () => "The content '" . $this->getRecord()->name . "' has been duplicated."),
            )
            ->successRedirectUrl(fn (Model $replica): string => route('filament.backstage.resources.content.edit', [
                'tenant' => Filament::getTenant(),
                'record' => $replica,
            ]));
    }
}
