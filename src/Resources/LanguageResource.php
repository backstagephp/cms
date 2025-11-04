<?php

namespace Backstage\Resources;

use Backstage\Models\Language;

class LanguageResource extends \Backstage\Translations\Filament\Resources\LanguageResource
{
    protected static ?string $model = Language::class;
}
