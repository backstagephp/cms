<?php

return [
    'resources' => [
        'language' => \Backstage\Translations\Filament\Resources\LanguageResource::class,
        'translation' => \Backstage\Translations\Filament\Resources\TranslationResource::class,
    ],

    'navigation' => [
        'group' => 'Manage',
    ],

    'eloquent' => [
        'translatable-models' => [
            \Backstage\Models\Media::class,
        ],
    ],
];
