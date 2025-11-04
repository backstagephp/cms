<?php

namespace Backstage\Widgets;

use Backstage\Models\FormSubmission;
use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class FormSubmissionsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 1;

    public function table(Table $table): Table
    {
        return $table
            ->heading('Newly submitted forms')
            ->query(
                FormSubmission::query()
                    ->where('site_ulid', Filament::getTenant()->getKey())
                    ->latest()
            )
            ->columns([
                TextColumn::make('title')
                    ->label('Title'),
            ])
            ->defaultPaginationPageOption(5);
    }
}
