<?php

namespace Backstage\Resources\SiteResource\Pages;

use Backstage\Resources\SiteResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSite extends EditRecord
{
    protected static string $resource = SiteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        // Force refresh the page to apply the new color theme
        $this->redirect($this->getResource()::getUrl('edit', ['record' => $this->record]));
    }
}
