<?php

use Backstage\Models\Content;
use Backstage\Models\Site;
use Backstage\Models\User;

return [
    /*
    |--------------------------------------------------------------------------
    | File upload
    |--------------------------------------------------------------------------
    |
    */
    'accepted_file_types' => [
        'image/jpeg',
        'image/png',
        'image/webp',
        'image/svg+xml',
        'application/pdf',
    ],

    'directory' => 'media',

    'disk' => env('FILAMENT_FILESYSTEM_DISK', 'public'),

    'should_preserve_filenames' => false,

    'should_register_navigation' => true,

    'visibility' => 'public',

    /*
    |--------------------------------------------------------------------------
    | Tenancy
    |--------------------------------------------------------------------------
    |
    */
    'is_tenant_aware' => true,
    'tenant_ownership_relationship_name' => 'site',
    'tenant_relationship' => 'site',
    'tenant_model' => Site::class,

    /*
    |--------------------------------------------------------------------------
    | Model and resource
    |--------------------------------------------------------------------------
    |
    */
    'model' => \Backstage\Media\Models\Media::class,

    'user_model' => User::class,

    'resources' => [
        'label' => 'Media',
        'plural_label' => 'Media',
        'navigation_group' => null,
        'navigation_label' => 'Media',
        'navigation_icon' => 'heroicon-o-photo',
        'navigation_sort' => null,
        'navigation_count_badge' => false,
        'resource' => \Backstage\Resources\MediaResource::class,
    ],

    'file_upload' => [
        'models' => [
            Content::class,
        ],
    ],
];
