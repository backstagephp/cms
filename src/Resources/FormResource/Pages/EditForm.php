<?php

namespace Backstage\Resources\FormResource\Pages;

use Backstage\Resources\FormResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Facades\Filament;
use Filament\Resources\Pages\EditRecord;

class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('Submissions')
                ->label(__('Submissions'))
                ->url(route('filament.backstage.resources.form-submissions.index', ['tenant' => Filament::getTenant(), 'filters' => ['form_slug' => ['values' => [$this->record->slug]]]])),
            DeleteAction::make(),
        ];
    }
}
