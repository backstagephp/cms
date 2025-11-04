<?php

use Backstage\Fields\Models\Field;
use Backstage\Models\Content;
use Backstage\Models\ContentFieldValue;
use Backstage\Models\Language;
use Backstage\Models\Site;
use Backstage\Models\Type;
use Illuminate\Support\Str;

test('content field value returns html string for simple string', function () {
    $site = Site::factory()->create();
    $language = Language::factory([
        'name' => 'English',
        'native' => 'English',
        'code' => 'en',
        'default' => true,
    ])->create();

    $type = Type::factory()->state([
        'name' => $name = 'Page',
        'name_plural' => Str::plural($name),
        'slug' => Str::slug($name),
        'icon' => 'document',
        'name_field' => 'title',
        'body_field' => 'body',
        'public' => true,
    ])
        ->has(Field::factory(1, [
            'name' => 'Title',
            'slug' => 'title',
            'field_type' => 'text',
            'position' => 1,
        ]))
        ->hasAttached($site)
        ->create();

    $content = Content::factory([
        'name' => 'Contact',
        'slug' => 'contact',
        'path' => 'contact',
        'type_slug' => $type->slug,
        'site_ulid' => $site->ulid,
        'language_code' => $language->code,
        'meta_tags' => ['title' => 'Contact'],
    ])
        ->has(ContentFieldValue::factory(1, [
            'field_ulid' => Field::where('slug', 'title')->where('model_type', 'type')->where('model_key', 'page')->first()?->ulid,
            'value' => $titleValue = 'Contact',
        ]), 'values')
        ->create();

    expect($content->field('title'))
        ->toBeInstanceOf(\Illuminate\Support\HtmlString::class)
        ->toEqual(new \Illuminate\Support\HtmlString($titleValue));
});

test('content field value returns html string for rich editor content', function () {
    $site = Site::factory()->create();
    $language = Language::factory([
        'name' => 'English',
        'native' => 'English',
        'code' => 'en',
        'default' => true,
    ])->create();

    $type = Type::factory()->state([
        'name' => $name = 'Page',
        'name_plural' => Str::plural($name),
        'slug' => Str::slug($name),
        'icon' => 'document',
        'name_field' => 'title',
        'body_field' => 'body',
        'public' => true,
    ])
        ->has(Field::factory(1, [
            'name' => 'Body',
            'slug' => 'body',
            'field_type' => 'rich-editor',
            'position' => 1,
        ]))
        ->hasAttached($site)
        ->create();

    $richEditorContent = [
        'type' => 'doc',
        'content' => [
            [
                'type' => 'paragraph',
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $paragraphValue = 'Hello World',
                    ],
                ],
            ],
        ],
    ];

    $content = Content::factory([
        'name' => 'About',
        'slug' => 'about',
        'path' => 'about',
        'type_slug' => $type->slug,
        'site_ulid' => $site->ulid,
        'language_code' => $language->code,
        'meta_tags' => ['title' => 'About'],
    ])
        ->has(ContentFieldValue::factory(1, [
            'field_ulid' => Field::where('slug', 'body')->where('model_type', 'type')->where('model_key', 'page')->first()?->ulid,
            'value' => json_encode($richEditorContent),
        ]), 'values')
        ->create();

    expect($content->field('body'))
        ->toBeInstanceOf(\Illuminate\Support\HtmlString::class)
        ->toEqual(new \Illuminate\Support\HtmlString('<p>' . $paragraphValue . '</p>'));
});

test('content field value returns boolean for checked checkbox', function () {
    $site = Site::factory()->create();
    $language = Language::factory([
        'name' => 'English',
        'native' => 'English',
        'code' => 'en',
        'default' => true,
    ])->create();

    $type = Type::factory()->state([
        'name' => $name = 'Page',
        'name_plural' => Str::plural($name),
        'slug' => Str::slug($name),
        'icon' => 'document',
        'name_field' => 'title',
        'body_field' => 'body',
        'public' => true,
    ])
        ->has(Field::factory(1, [
            'name' => 'Featured',
            'slug' => 'featured',
            'field_type' => 'checkbox',
            'position' => 1,
        ]))
        ->hasAttached($site)
        ->create();

    $content = Content::factory([
        'name' => 'Featured Post',
        'slug' => 'featured-post',
        'path' => 'featured-post',
        'type_slug' => $type->slug,
        'site_ulid' => $site->ulid,
        'language_code' => $language->code,
        'meta_tags' => ['title' => 'Featured Post'],
    ])
        ->has(ContentFieldValue::factory(1, [
            'field_ulid' => Field::where('slug', 'featured')->where('model_type', 'type')->where('model_key', 'page')->first()?->ulid,
            'value' => '1',
        ]), 'values')
        ->create();

    expect($content->field('featured'))
        ->toBeBool()
        ->toEqual(true);
});

test('content field value returns related content for select with relation', function () {
    $site = Site::factory()->create();
    $language = Language::factory([
        'name' => 'English',
        'native' => 'English',
        'code' => 'en',
        'default' => true,
    ])->create();

    // Create a content that will be referenced
    $relatedContent = Content::factory([
        'name' => 'Related Page',
        'slug' => 'related-page',
        'path' => 'related-page',
        'site_ulid' => $site->ulid,
        'language_code' => $language->code,
        'meta_tags' => ['title' => 'Related Page'],
    ])->create();

    $type = Type::factory()->state([
        'name' => $name = 'Page',
        'name_plural' => Str::plural($name),
        'slug' => Str::slug($name),
        'icon' => 'document',
        'name_field' => 'title',
        'body_field' => 'body',
        'public' => true,
    ])
        ->has(Field::factory(1, [
            'name' => 'Related Content',
            'slug' => 'related_content',
            'field_type' => 'select',
            'position' => 1,
            'config' => [
                'hint' => null,
                'hidden' => false,
                'prefix' => null,
                'suffix' => null,
                'disabled' => false,
                'multiple' => false,
                'required' => false,
                'allowHtml' => false,
                'relations' => [[
                    'resource' => 'content',
                    'relationKey' => 'ulid',
                    'relationValue' => 'name',
                    'relationKey_options' => [
                        'ulid' => 'Ulid',
                        'name' => 'Name',
                    ],
                    'relationValue_options' => [
                        'ulid' => 'Ulid',
                        'name' => 'Name',
                    ],
                    'relationValue_filters' => [[
                        'value' => null,
                        'column' => null,
                        'operator' => null,
                    ]],
                ]],
                'helperText' => null,
                'optionType' => ['relationship'],
                'prefixIcon' => null,
                'searchable' => false,
                'suffixIcon' => null,
                'prefixIconColor' => null,
                'suffixIconColor' => null,
                'validationRules' => [],
                'visibilityRules' => [],
                'selectablePlaceholder' => true,
            ],
        ]))
        ->hasAttached($site)
        ->create();

    $content = Content::factory([
        'name' => 'Main Page',
        'slug' => 'main-page',
        'path' => 'main-page',
        'type_slug' => $type->slug,
        'site_ulid' => $site->ulid,
        'language_code' => $language->code,
        'meta_tags' => ['title' => 'Main Page'],
    ])
        ->has(ContentFieldValue::factory(1, [
            'field_ulid' => Field::where('slug', 'related_content')->where('model_type', 'type')->where('model_key', 'page')->first()?->ulid,
            'value' => $relatedContent->ulid,
        ]), 'values')
        ->create();

    $relatedContentCollection = $content->field('related_content');
    expect($relatedContentCollection)
        ->toBeInstanceOf(Content::class)
        ->ulid->toBe($relatedContent->ulid);
});

test('content field value returns multiple related content for select with multiple relations', function () {
    $site = Site::factory()->create();
    $language = Language::factory([
        'name' => 'English',
        'native' => 'English',
        'code' => 'en',
        'default' => true,
    ])->create();

    // Create two contents that will be referenced
    $relatedContent1 = Content::factory([
        'name' => 'Related Page 1',
        'slug' => 'related-page-1',
        'path' => 'related-page-1',
        'site_ulid' => $site->ulid,
        'language_code' => $language->code,
        'meta_tags' => ['title' => 'Related Page 1'],
    ])->create();

    $relatedContent2 = Content::factory([
        'name' => 'Related Page 2',
        'slug' => 'related-page-2',
        'path' => 'related-page-2',
        'site_ulid' => $site->ulid,
        'language_code' => $language->code,
        'meta_tags' => ['title' => 'Related Page 2'],
    ])->create();

    $type = Type::factory()->state([
        'name' => $name = 'Page',
        'name_plural' => Str::plural($name),
        'slug' => Str::slug($name),
        'icon' => 'document',
        'name_field' => 'title',
        'body_field' => 'body',
        'public' => true,
    ])
        ->has(Field::factory(1, [
            'name' => 'Related Contents',
            'slug' => 'related_contents',
            'field_type' => 'select',
            'position' => 1,
            'config' => [
                'hint' => null,
                'hidden' => false,
                'prefix' => null,
                'suffix' => null,
                'disabled' => false,
                'multiple' => true,
                'required' => false,
                'allowHtml' => false,
                'relations' => [[
                    'resource' => 'content',
                    'relationKey' => 'ulid',
                    'relationValue' => 'name',
                    'relationKey_options' => [
                        'ulid' => 'Ulid',
                        'name' => 'Name',
                    ],
                    'relationValue_options' => [
                        'ulid' => 'Ulid',
                        'name' => 'Name',
                    ],
                    'relationValue_filters' => [[
                        'value' => null,
                        'column' => null,
                        'operator' => null,
                    ]],
                ]],
                'helperText' => null,
                'optionType' => ['relationship'],
                'prefixIcon' => null,
                'searchable' => false,
                'suffixIcon' => null,
                'prefixIconColor' => null,
                'suffixIconColor' => null,
                'validationRules' => [],
                'visibilityRules' => [],
                'selectablePlaceholder' => true,
            ],
        ]))
        ->hasAttached($site)
        ->create();

    $content = Content::factory([
        'name' => 'Main Page',
        'slug' => 'main-page',
        'path' => 'main-page',
        'type_slug' => $type->slug,
        'site_ulid' => $site->ulid,
        'language_code' => $language->code,
        'meta_tags' => ['title' => 'Main Page'],
    ])
        ->has(ContentFieldValue::factory(1, [
            'field_ulid' => Field::where('slug', 'related_contents')->where('model_type', 'type')->where('model_key', 'page')->first()?->ulid,
            'value' => json_encode([$relatedContent1->ulid, $relatedContent2->ulid]),
        ]), 'values')
        ->create();

    $relatedContentsCollection = $content->field('related_contents');
    expect($relatedContentsCollection)
        ->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class)
        ->toHaveCount(2)
        ->sequence(
            fn ($content) => $content->ulid->toBe($relatedContent1->ulid),
            fn ($content) => $content->ulid->toBe($relatedContent2->ulid)
        );
});

test('content field value returns multiple related content for checkbox-list with relations', function () {
    $site = Site::factory()->create();
    $language = Language::factory([
        'name' => 'English',
        'native' => 'English',
        'code' => 'en',
        'default' => true,
    ])->create();

    // Create two contents that will be referenced
    $relatedContent1 = Content::factory([
        'name' => 'Related Page 1',
        'slug' => 'related-page-1',
        'path' => 'related-page-1',
        'site_ulid' => $site->ulid,
        'language_code' => $language->code,
        'meta_tags' => ['title' => 'Related Page 1'],
    ])->create();

    $relatedContent2 = Content::factory([
        'name' => 'Related Page 2',
        'slug' => 'related-page-2',
        'path' => 'related-page-2',
        'site_ulid' => $site->ulid,
        'language_code' => $language->code,
        'meta_tags' => ['title' => 'Related Page 2'],
    ])->create();

    $type = Type::factory()->state([
        'name' => $name = 'Page',
        'name_plural' => Str::plural($name),
        'slug' => Str::slug($name),
        'icon' => 'document',
        'name_field' => 'title',
        'body_field' => 'body',
        'public' => true,
    ])
        ->has(Field::factory(1, [
            'name' => 'Related Contents',
            'slug' => 'related_contents',
            'field_type' => 'checkbox-list',
            'position' => 1,
            'config' => [
                'hint' => null,
                'hidden' => false,
                'prefix' => null,
                'suffix' => null,
                'disabled' => false,
                'required' => false,
                'allowHtml' => false,
                'relations' => [[
                    'resource' => 'content',
                    'relationKey' => 'ulid',
                    'relationValue' => 'name',
                    'relationKey_options' => [
                        'ulid' => 'Ulid',
                        'name' => 'Name',
                    ],
                    'relationValue_options' => [
                        'ulid' => 'Ulid',
                        'name' => 'Name',
                    ],
                    'relationValue_filters' => [[
                        'value' => null,
                        'column' => null,
                        'operator' => null,
                    ]],
                ]],
                'helperText' => null,
                'optionType' => ['relationship'],
                'prefixIcon' => null,
                'searchable' => false,
                'suffixIcon' => null,
                'prefixIconColor' => null,
                'suffixIconColor' => null,
                'validationRules' => [],
                'visibilityRules' => [],
                'selectablePlaceholder' => true,
            ],
        ]))
        ->hasAttached($site)
        ->create();

    $content = Content::factory([
        'name' => 'Main Page',
        'slug' => 'main-page',
        'path' => 'main-page',
        'type_slug' => $type->slug,
        'site_ulid' => $site->ulid,
        'language_code' => $language->code,
        'meta_tags' => ['title' => 'Main Page'],
    ])
        ->has(ContentFieldValue::factory(1, [
            'field_ulid' => Field::where('slug', 'related_contents')->where('model_type', 'type')->where('model_key', 'page')->first()?->ulid,
            'value' => json_encode([$relatedContent1->ulid, $relatedContent2->ulid]),
        ]), 'values')
        ->create();

    $relatedContentsCollection = $content->field('related_contents');
    expect($relatedContentsCollection)
        ->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class)
        ->toHaveCount(2)
        ->sequence(
            fn ($content) => $content->ulid->toBe($relatedContent1->ulid),
            fn ($content) => $content->ulid->toBe($relatedContent2->ulid)
        );
});
