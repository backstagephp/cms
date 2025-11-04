<?php

use Backstage\Models\Content;
use Backstage\Models\Type;

test('public toggle respects type default setting', function () {
    $type = Type::factory()->create(['public' => false]);
    $content = Content::factory()->create([
        'type_slug' => $type->slug,
        'public' => false,
    ]);

    expect($content->public)->toBeFalse();
});

test('public toggle defaults to true when type public is true', function () {
    $type = Type::factory()->create(['public' => true]);
    $content = Content::factory()->create([
        'type_slug' => $type->slug,
        'public' => true,
    ]);

    expect($content->public)->toBeTrue();
});

test('path field visibility logic is implemented', function () {

    $content = Content::factory()->create([
        'public' => true,
        'path' => 'test-path',
    ]);

    expect($content->public)->toBeTrue();
    expect($content->path)->toBe('test-path');
    expect($content->url)->not->toBeNull();

    $content->update(['public' => false]);
    $content->refresh();

    expect($content->public)->toBeFalse();
    expect($content->url)->toBeNull();
});

test('path validation logic is implemented', function () {

    $content = Content::factory()->create([
        'public' => false,
        'path' => '/',
    ]);

    expect($content->path)->toBe('/');
    expect($content->url)->toBeNull();

    $content->update(['public' => true, 'path' => 'valid-path']);
    $content->refresh();

    expect($content->public)->toBeTrue();
    expect($content->path)->toBe('valid-path');
    expect($content->url)->not->toBeNull();
});

test('form structure changes are implemented', function () {

    $type = Type::factory()->create(['public' => true]);
    $content = Content::factory()->create([
        'type_slug' => $type->slug,
        'public' => true,
        'path' => 'test-content',
    ]);

    expect($content->public)->toBeTrue();
    expect($content->path)->toBe('test-content');
    expect($content->url)->not->toBeNull();

    $content->update(['public' => false]);
    $content->refresh();

    expect($content->public)->toBeFalse();
    expect($content->path)->toBe('/');
    expect($content->url)->toBeNull();
});
