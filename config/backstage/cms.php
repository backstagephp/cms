<?php

use Backstage\Media\MediaPlugin;
use Backstage\Translations\Filament\TranslationsPlugin;

return [
    'panel' => [
        'default' => true,

        'plugins' => [
            Backstage\Redirects\Filament\RedirectsPlugin::make(),
            MediaPlugin::make(),
            TranslationsPlugin::make(),
        ],

        'resources' => [
            Backstage\Resources\BlockResource::class,
            Backstage\Resources\ContentResource::class,
            Backstage\Resources\DomainResource::class,
            Backstage\Resources\FieldResource::class,
            Backstage\Resources\FormResource::class,
            Backstage\Resources\FormSubmissionResource::class,
            Backstage\Resources\MenuResource::class,
            Backstage\Resources\LanguageResource::class,
            Backstage\Resources\SettingResource::class,
            Backstage\Resources\SiteResource::class,
            Backstage\Resources\TagResource::class,
            Backstage\Media\Resources\MediaResource::class,
            // Backstage\Resources\TemplateResource::class,
            Backstage\Resources\TypeResource::class,
            Backstage\Resources\UserResource::class,

            Backstage\Translations\Filament\Resources\TranslationResource::class,
        ],

        'widgets' => [
            Backstage\Widgets\ContentUpdatesWidget::class,
            Backstage\Widgets\FormSubmissionsWidget::class,
        ],
    ],

    'components' => [
        'blocks' => [
            Backstage\View\Components\Blocks\Heading::class,
            Backstage\View\Components\Blocks\Paragraph::class,
            Backstage\View\Components\Form::class,
        ],
    ],

    // Can be used to override the default file upload field user in Backstage.
    // Example: 'default_file_upload_field' => \Backstage\Uploadcare\Forms\Components\Uploadcare::class,
    'default_file_upload_field' => \Backstage\Fields\Fields\FileUpload::class,
];
