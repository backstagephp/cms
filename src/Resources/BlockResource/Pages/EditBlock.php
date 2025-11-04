<?php

namespace Backstage\Resources\BlockResource\Pages;

use Backstage\Models\Block;
use Backstage\Resources\BlockResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class EditBlock extends EditRecord
{
    protected static string $resource = BlockResource::class;

    protected function getHeaderActions(): array
    {
        $actions = [];
        $actions[] = Action::make('Create Component')
            ->requiresConfirmation()
            ->action(function (Block $record) {
                Artisan::call('make:component', ['name' => Str::studly($record->slug)]);

                if (file_exists(app_path('View/Components/' . Str::studly($this->record->slug) . '.php'))) {

                    // Replace __construct with params
                    $component = file_get_contents(app_path('View/Components/' . Str::studly($this->record->slug) . '.php'));
                    $params = '';
                    foreach ($this->record->fields as $field) {
                        $type = 'string';
                        $defaultValue = "''";
                        if ($field->config['multiple'] ?? false) {
                            $type = 'array';
                            $defaultValue = '[]';
                        }
                        $params .= 'public ' . $type . ' $' . $field->slug . '';
                        if (! $field->config['required']) {
                            $params .= ' = ' . $defaultValue . ', ';
                        } else {
                            $params .= ', ';
                        }
                    }
                    $params = rtrim($params, ', ');
                    $component = str_replace('__construct()', '__construct(' . $params . ')', $component);
                    file_put_contents(app_path('View/Components/' . Str::studly($this->record->slug) . '.php'), $component);
                }
                Notification::make()
                    ->title(__('Component created at :location', ['location' => app_path('View/Components/' . Str::studly($this->record->slug) . '.php')]))
                    ->success()
                    ->send();

                return $record;
            });

        $actions[] = DeleteAction::make()
            ->before(function (Block $record) {
                $record->sites()->detach();
            });

        return $actions;
    }
}
