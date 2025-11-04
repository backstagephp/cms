<?php

namespace Backstage\Resources\FormSubmissionResource\Pages;

use Backstage\Resources\FormSubmissionResource;
use Filament\Resources\Pages\ListRecords;

class ListFormSubmissions extends ListRecords
{
    protected static string $resource = FormSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
