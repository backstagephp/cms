<?php

use Backstage\Models\Content;

test('content can have a parent', function () {
    $parent = Content::factory()->create();

    $content = Content::factory()->create([
        'parent_ulid' => $parent->ulid,
        'site_ulid' => $parent->site_ulid,
        'language_code' => $parent->language_code,
    ]);

    expect($content->ancestors()->count())->toBe(1);
});

test('content can have multiple ancestors', function () {
    $root = Content::factory()->create();

    $parent = Content::factory()->create([
        'parent_ulid' => $root->ulid,
        'site_ulid' => $root->site_ulid,
        'language_code' => $root->language_code,
    ]);

    $content = Content::factory()->create([
        'parent_ulid' => $parent->ulid,
        'site_ulid' => $parent->site_ulid,
        'language_code' => $parent->language_code,
    ]);

    expect($content->ancestors()->count())->toBe(2);
});
