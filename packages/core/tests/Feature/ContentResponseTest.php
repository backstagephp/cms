<?php

use Backstage\Models\Content;
use Backstage\Models\Site;

test('confirm public content is found', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory()
            ->withDomain('localhost'),
        'path' => 'welcome',
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => []]);

    expect($content->url)->toBe('https://localhost/welcome');

    $this->get($content->url)
        ->assertOk()
        ->assertSee($content->title);
});

test('confirm public content with site path is found', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory([
            'path' => 'site',
        ])
            ->withDomain('localhost'),
        'path' => 'news',
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => []]);

    expect($content->url)->toBe('https://localhost/site/news');

    $this->get($content->url)
        ->assertOk()
        ->assertSee($content->title);
});

test('confirm content without publication date is not accessible on frontend', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory()
            ->withDomain('localhost'),
        'path' => 'draft-page',
        'published_at' => null, // No publication date
        'public' => true,
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => []]);

    // Content should not be accessible on frontend
    $this->get($content->url)
        ->assertNotFound();
});

test('confirm content with future publication date is not accessible on frontend', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory()
            ->withDomain('localhost'),
        'path' => 'future-page',
        'published_at' => now()->addDays(1), // Future publication date
        'public' => true,
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => []]);

    // Content should not be accessible on frontend
    $this->get($content->url)
        ->assertNotFound();
});

test('confirm content with past publication date is accessible on frontend', function () {
    $content = Content::factory()->create([
        'site_ulid' => Site::factory()
            ->withDomain('localhost'),
        'path' => 'published-page',
        'published_at' => now()->subDays(1), // Past publication date
        'public' => true,
    ]);

    $content->site->domains()->first()->languages()->attach([$content->language_code => []]);

    // Content should be accessible on frontend
    $this->get($content->url)
        ->assertOk()
        ->assertSee($content->title);
});
