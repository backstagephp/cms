<?php

namespace Backstage\Media\Resources\MediaResource;

use Backstage\Media\MediaPlugin;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMedia extends EditRecord
{
    public static function getResource(): string
    {
        return MediaPlugin::get()->getResource();
    }

    public function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->action('save')
                ->label(__('Save')),
            Action::make('preview')
                ->label(__('Preview'))
                ->color('gray')
                ->url(fn () => (is_object($this->record) && property_exists($this->record, 'url')) ? $this->record->url : null, shouldOpenInNewTab: true),
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        //
    }
}
