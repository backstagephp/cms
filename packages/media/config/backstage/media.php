<?php

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
        'video/mp4',
        'video/webm',
        'audio/mpeg',
        'audio/ogg',
        'application/pdf',
    ],

    'directory' => 'media',

    'disk' => config('filesystems.default', 'public'),

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
    'tenant_ownership_relationship_name' => 'tenant',
    'tenant_relationship' => 'tenant',
    // 'tenant_model' => \App\Models\Tenant::class,

    /*
    |--------------------------------------------------------------------------
    | Model and resource
    |--------------------------------------------------------------------------
    |
    */
    'model' => \Backstage\Media\Models\Media::class,

    // 'user_model' => \App\Models\User::class,

    'resources' => [
        'label' => 'Media',
        'plural_label' => 'Media',
        'navigation_group' => null,
        'navigation_label' => 'Media',
        'navigation_icon' => 'heroicon-o-photo',
        'navigation_sort' => null,
        'navigation_count_badge' => false,
        'resource' => \Backstage\Media\Resources\MediaResource::class,
    ],
];
