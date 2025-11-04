<?php

use Backstage\Models\Content;
use Backstage\Models\Site;

test('preview functionality works for public content', function () {
    $content = Content::factory()->create([
        'public' => true,
        'path' => 'test-content',
        'site_ulid' => Site::factory()->withDomain('localhost'),
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => []]);

    expect($content->url)->not->toBeNull();
    expect($content->url)->toContain('test-content');
});

test('preview functionality is disabled for private content', function () {
    $content = Content::factory()->create([
        'public' => false,
        'path' => null,
    ]);

    expect($content->url)->toBeNull();
});

test('preview URL is accessible for public content', function () {
    $content = Content::factory()->create([
        'public' => true,
        'path' => 'preview-test',
        'site_ulid' => Site::factory()->withDomain('localhost'),
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => []]);

    $this->get($content->url)
        ->assertOk();
});

test('preview URL is not accessible for private content', function () {
    $content = Content::factory()->create([
        'public' => false,
        'path' => 'private-preview-test',
        'site_ulid' => Site::factory()->withDomain('localhost'),
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => []]);

    $this->get('https://localhost/private-preview-test')
        ->assertNotFound();
});

test('preview behavior changes when content public status changes', function () {
    $content = Content::factory()->create([
        'public' => true,
        'path' => 'toggle-test',
        'site_ulid' => Site::factory()->withDomain('localhost'),
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => []]);

    expect($content->url)->not->toBeNull();

    $content->update(['public' => false]);
    $content->refresh();

    expect($content->url)->toBeNull();

    $content->update(['public' => true, 'path' => 'toggle-test']);
    $content->refresh();

    expect($content->url)->not->toBeNull();
});

test('preview URL generation respects site configuration', function () {
    $content = Content::factory()->create([
        'public' => true,
        'path' => 'site-config-test',
        'site_ulid' => Site::factory([
            'path' => 'site-path',
        ])->withDomain('localhost'),
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => []]);

    expect($content->url)->toContain('site-path');
    expect($content->url)->toContain('site-config-test');
});

test('preview functionality is implemented in EditContent page', function () {
    expect(class_exists(\Backstage\Resources\ContentResource\Pages\EditContent::class))->toBeTrue();

    $page = new \Backstage\Resources\ContentResource\Pages\EditContent;
    expect($page)->toBeInstanceOf(\Filament\Resources\Pages\EditRecord::class);
});
