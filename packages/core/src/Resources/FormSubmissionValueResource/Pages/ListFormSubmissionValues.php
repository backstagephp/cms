<?php

namespace Backstage\Resources\FormSubmissionValueResource\Pages;

use Backstage\Resources\FormResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListFormSubmissionValues extends ListRecords
{
    protected static string $resource = FormResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
