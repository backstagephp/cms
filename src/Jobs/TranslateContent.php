<?php

namespace Backstage\Jobs;

use Backstage\Models\Content;
use Backstage\Models\ContentFieldValue;
use Backstage\Models\Language;
use Backstage\Translations\Laravel\Domain\Translatables\Actions\TranslateAttribute;
use Backstage\Translations\Laravel\Facades\Translator;
use Exception;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class TranslateContent implements ShouldQueue
{
    use Batchable;
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $timeout = 3600;

    public $tries = 2;

    public $contentUlid = null;

    public $duplicateContent = null;

    public function __construct(
        public Content $content,
        public Language $language
    ) {}

    public function handle(): void
    {
        if ($this->content->language_code === $this->language->code || $exsistingContent = Content::query()->where('slug', $this->content->slug)->where('type_slug', $this->content->type_slug)->where('language_code', $this->language->code)->first()) {
            $this->contentUlid = $exsistingContent?->ulid;

            return;
        }

        $duplicatedContent = $this->content->replicate(['ulid']);
        $duplicatedContent->language_code = $this->language->code;
        $duplicatedContent->meta_tags = [];
        $duplicatedContent->edited_at = now();
        $duplicatedContent->saveQuietly();

        $this->duplicateContent = $duplicatedContent;

        $parentTranslationUlid = null;

        if ($this->content->parent_ulid) {
            $parent = Content::where('ulid', $this->content->parent_ulid)->first();

            if ($parent) {
                $parentTranslation = Content::where('slug', $parent->slug)
                    ->where('language_code', $this->language->code)
                    ->first();

                if (! $parentTranslation) {
                    $newInstance = new self($parent, $this->language);

                    $newInstance->handle();

                    $parentTranslationUlid = $newInstance->contentUlid;
                } else {
                    $parentTranslationUlid = $parentTranslation->ulid;
                }
            }
        }

        if ($parentTranslationUlid) {
            $this->duplicateContent->parent_ulid = $parentTranslationUlid;
        }

        // Translate name and path
        if ($this->content->name) {
            $this->duplicateContent->name = Translator::translate(
                $this->content->name,
                $this->language->code
            );
        }

        if ($this->content->path && $this->content->path !== '/') {
            // $fullPath = '';
            // foreach ($duplicatedContent->ancestors as $ancestor) {
            //     if ($ancestor->language_code === $this->language->code) {
            //         $fullPath .= $ancestor->path . '/';
            //     }
            // }

            // $path = explode('/', $this->content->path);
            // $contentPath = end($path);

            // if($contentPath !==null || $content )
            // $translatedPath = Translator::translate(
            //     $contentPath,
            //     $this->language->code,
            //     $this->getExtraPrompt()

            // );
            // $duplicatedContent->path = rtrim($fullPath . $translatedPath, '/');

            $parentPath = $duplicatedContent->parent?->path;

            $path = '';
            if ($parentPath) {
                $path = $parentPath . '/';
            }

            $translatablePath = str($this->content->path)->afterLast('/');

            $translatedContentPath = Translator::translate(
                $translatablePath,
                $this->language->code
            );

            $path = $path . $translatedContentPath;

            $duplicatedContent->path = $path;
        }

        if (! empty($this->content->meta_tags)) {
            $duplicatedContent->meta_tags = TranslateAttribute::translateArray(
                model: null,
                attribute: null,
                data: $this->content->meta_tags,
                targetLanguage: $this->language->code,
                rules: ['!robots', 'title', 'description', 'keywords.*'],
                extraPrompt: $this->getExtraPrompt()
            );
        }

        $this->content->values()->with('field')->get()->each(function (ContentFieldValue $value) {
            $duplicatedValue = $value->replicate(['ulid']);
            $duplicatedValue->content_ulid = $this->duplicateContent->ulid;

            if ($this->isJson($value->value)) {
                $array = json_decode($value->value, true);

                if (! is_int($array)) {
                    // Translate relationships
                    if (($value->field?->config['relations'][0]['resource'] ?? '') == 'content') {
                        $translatedArray = [];
                        foreach ($array as $contentId) {
                            $relatedContent = Content::where('ulid', $contentId)->first();

                            if ($relatedContent) {
                                $newInstance = new self($relatedContent, $this->language);
                                $newInstance->handle();

                                if ($relationUlid = $newInstance->contentUlid) {
                                    $translatedArray[] = $relationUlid;
                                }
                            }
                        }
                    } else {
                        // Translate data (html)
                        $translatedArray = TranslateAttribute::translateArray(
                            model: null,
                            attribute: null,
                            targetLanguage: $this->duplicateContent->language_code,
                            data: $array,
                            rules: ['*data'],
                            extraPrompt: $this->getExtraPrompt()
                        );
                    }
                    $duplicatedValue->value = json_encode($translatedArray, JSON_UNESCAPED_UNICODE);
                } else {
                    $duplicatedValue->value = Translator::translate(
                        $value->value,
                        $this->duplicateContent->language_code
                    );
                }
            } elseif (! empty($value->value)) {
                $duplicatedValue->value = Translator::translate(
                    $value->value,
                    $this->duplicateContent->language_code
                );
            }

            $duplicatedValue->saveQuietly();
        });

        $this->duplicateContent->saveQuietly();

        $this->contentUlid = $this->duplicateContent->ulid;
    }

    protected function isJson($value): bool
    {
        if (! is_string($value)) {
            return false;
        }

        json_decode($value);

        return json_last_error() === JSON_ERROR_NONE;
    }

    protected function getExtraPrompt()
    {
        return isset($this->content->type->name) ? 'This translation is in the context of ' . $this->content->type->name : '';
    }

    public function middleware(): array
    {
        return [
            (new WithoutOverlapping($this->content->ulid . '-' . $this->language->code))
                ->expireAfter(1800),
        ];
    }

    public function tags()
    {
        return ['translating:' . $this->language->code, 'content:' . $this->content->ulid];
    }

    public function failed(Exception $exception): void
    {
        Log::error('Job failed', [
            'job' => static::class,
            'target_content' => $this->content->ulid,
            'target_language' => $this->language->code,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        if ($this->duplicateContent !== null && $this->duplicateContent instanceof Content) {
            $this->duplicateContent->delete();
        }
    }
}
