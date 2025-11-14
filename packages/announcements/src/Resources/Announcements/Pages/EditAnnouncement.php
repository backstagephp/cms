<?php

namespace Backstage\Announcements\Resources\Announcements\Pages;

use Backstage\Announcements\Resources\Announcements\AnnouncementResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAnnouncement extends EditRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reset-dismissals')
                ->label(__('Reset dismissals'))
                ->requiresConfirmation()
                ->color('warning')
                ->action(function () {
                    $this->getRecord()->dismissals()->delete();
                })
                ->after(function() {
                    Notification::make()
                        ->title(__('Dismissals reset'))
                        ->body(__('All dismissals for this announcement have been reset.'))
                        ->success()
                        ->send();
                }),

            ViewAction::make(),

            DeleteAction::make(),

            ForceDeleteAction::make(),

            RestoreAction::make(),

        ];
    }
}
