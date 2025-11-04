<?php

namespace Backstage\Resources\FormSubmissionResource\Pages;

use Backstage\Resources\FormSubmissionResource;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Database\Eloquent\Model;

class ViewFormSubmission extends ViewRecord
{
    protected static string $resource = FormSubmissionResource::class;

    public function getRecord(): Model
    {
        return parent::getRecord()->load(['values.field']);
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('notes')
                ->label(__('Add Notes'))
                ->fillForm([
                    'notes' => $this->record->notes,
                ])
                ->schema([
                    Textarea::make('notes')
                        ->label(__('Notes'))
                        ->rows(3),
                ])
                ->modalSubmitActionLabel(__('Save'))
                ->action(function (array $data) {
                    $this->record->update([
                        'notes' => $data['notes'],
                    ]);

                    Notification::make()
                        ->title(__('Notes saved'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
