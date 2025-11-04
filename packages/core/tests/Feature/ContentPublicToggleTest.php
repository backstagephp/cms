<?php

use Backstage\Models\Content;
use Backstage\Models\Site;

test('public content has url and path', function () {
    $content = Content::factory()->create([
        'public' => true,
        'path' => 'test-content',
    ]);

    expect($content->url)->not->toBeNull();
    expect($content->path)->toBe('test-content');
});

test('private content has no url and no path', function () {
    $content = Content::factory()->create([
        'public' => false,
        'path' => null,
    ]);

    expect($content->url)->toBeNull();
    expect($content->path)->toBe('/');
});

test('setting content to private clears path', function () {
    $content = Content::factory()->create([
        'public' => true,
        'path' => 'test-content',
    ]);

    expect($content->path)->toBe('test-content');

    $content->update(['public' => false]);

    expect($content->fresh()->path)->toBe('/');
    expect($content->fresh()->url)->toBeNull();
});

test('setting content to public does not automatically generate path from name', function () {
    $content = Content::factory()->create([
        'public' => false,
        'path' => null,
        'name' => 'Test Content',
    ]);

    expect($content->path)->toBe('/');

    $content->update(['public' => true]);

    expect($content->fresh()->path)->toBe('/');
});

test('private content is not accessible via public routes', function () {
    $content = Content::factory()->create([
        'public' => false,
        'path' => 'private-content',
        'site_ulid' => Site::factory()->withDomain('localhost'),
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => []]);

    $this->get('https://localhost/private-content')
        ->assertNotFound();
});

test('public content is accessible via public routes', function () {
    $content = Content::factory()->create([
        'public' => true,
        'path' => 'public-content',
        'site_ulid' => Site::factory()->withDomain('localhost'),
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => []]);

    $this->get('https://localhost/public-content')
        ->assertOk();
});

test('content observer clears path when public is set to false', function () {
    $content = Content::factory()->create([
        'public' => true,
        'path' => 'test-path',
    ]);

    expect($content->path)->toBe('test-path');

    $content->public = false;
    $content->save();

    expect($content->fresh()->path)->toBe('/');
});

test('content with public false and existing path clears path on save', function () {
    $content = Content::factory()->create([
        'public' => false,
        'path' => 'should-be-cleared',
    ]);

    $content->path = 'should-be-cleared';
    $content->save();

    expect($content->fresh()->path)->toBe('/');
});

test('public scope only returns public content', function () {
    $publicContent = Content::factory()->create([
        'public' => true,
        'published_at' => now()->subDay(),
    ]);

    $privateContent = Content::factory()->create([
        'public' => false,
        'published_at' => now()->subDay(),
    ]);

    $publicResults = Content::public()->get();

    expect($publicResults)->toHaveCount(1);
    expect($publicResults->first()->ulid)->toBe($publicContent->ulid);
});

test('public scope excludes unpublished content even if public', function () {
    $publicUnpublishedContent = Content::factory()->create([
        'public' => true,
        'published_at' => now()->addDay(),
    ]);

    $publicPublishedContent = Content::factory()->create([
        'public' => true,
        'published_at' => now()->subDay(),
    ]);

    $publicResults = Content::public()->get();

    expect($publicResults)->toHaveCount(1);
    expect($publicResults->first()->ulid)->toBe($publicPublishedContent->ulid);
});
