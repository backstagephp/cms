<?php

namespace Backstage\Models;

use Backstage\Casts\ContentPathCast;
use Backstage\Concerns\DecodesJsonStrings;
use Backstage\Fields\Concerns\HasFields;
use Backstage\Fields\Models\Field;
use Backstage\Jobs\TranslateContent;
use Backstage\Media\Concerns\HasMedia;
use Backstage\Models\Concerns\HasContentRelations;
use Backstage\Observers\ContentDepthObserver;
use Backstage\Observers\ContentRevisionObserver;
use Backstage\Observers\ContentUrlObserver;
use Backstage\Shared\HasPackageFactory;
use Backstage\Shared\HasTags;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

/**
 * Backstage\Models\Content
 *
 * @property string $ulid
 * @property string $site_ulid
 * @property string $language_code
 * @property string $type_slug
 * @property string|null $template_slug
 * @property int|null $creator_id
 * @property string|null $parent_ulid
 * @property string $name
 * @property string $slug
 * @property string|null $path
 * @property array|null $meta_tags
 * @property array|null $microdata
 * @property string|null $password
 * @property bool $auth
 * @property bool $cache
 * @property bool $public
 * @property bool $pin
 * @property bool $lock
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 * @property \Carbon\Carbon|null $approved_at
 * @property \Carbon\Carbon|null $disapproved_at
 * @property \Carbon\Carbon|null $edited_at
 * @property \Carbon\Carbon|null $expired_at
 * @property \Carbon\Carbon|null $locked_at
 * @property \Carbon\Carbon|null $pinned_at
 * @property \Carbon\Carbon|null $published_at
 * @property \Carbon\Carbon|null $refreshed_at
 * @property \Carbon\Carbon|null $searchable_at
 * @property \Carbon\Carbon|null $deleted_at
 * @property string|null $url
 * @property string|null $parent_path
 * @property int $backstage_depth
 * @property string $view
 */
#[ObservedBy(ContentDepthObserver::class)]
#[ObservedBy(ContentUrlObserver::class)]
#[ObservedBy(ContentRevisionObserver::class)]
class Content extends Model
{
    use DecodesJsonStrings;
    use HasContentRelations;
    use HasFields;
    use HasMedia;
    use HasPackageFactory;
    use HasRecursiveRelationships;
    use HasTags;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $table = 'content';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'path' => ContentPathCast::class,
            'meta_tags' => 'array',
            'public' => 'boolean',
        ];
    }

    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'content_user', 'content_ulid', 'user_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'language_code', 'code');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function values(): HasMany
    {
        return $this->hasMany(ContentFieldValue::class)
            ->with('field');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(Version::class, 'content_ulid', 'ulid');
    }

    /**
     * Get formatted field values as a key-value array where keys are field ULIDs.
     * This method processes JSON values and returns them in a format suitable for forms.
     * Used in the EditContent page to fill the form with existing values and
     * in the ContentResource to fill the table with existing values, which is needed in
     * the ManageChildrenContent page.
     */
    public function getFormattedFieldValues(): array
    {
        return $this->values()->get()->mapWithKeys(function ($value) {
            if (! $value->field) {
                return [];
            }
            $value->value = json_decode($value->value, true) ?? $value->value;

            // Recursively decode nested JSON strings only for repeater and builder fields
            if (in_array($value->field->field_type, ['repeater', 'builder'])) {
                $value->value = $this->decodeAllJsonStrings($value->value);
            }

            return [$value->field->ulid => $value->value];
        })->toArray();
    }

    public function fields(): HasManyThrough
    {
        return $this->hasManyThrough(
            Field::class,
            Type::class,
            'slug',
            'model_key',
            'type_slug',
            'slug'
        )->where('model_type', 'type');
    }

    /**
     * @return Attribute<Provider, string>
     */
    protected function pageTitle(): Attribute
    {
        return Attribute::make(
            get: fn () => html_entity_decode(
                implode(
                    ' ',
                    array_filter([$this->meta_tags['title'], $this->site->title_separator, $this->site->title]),
                ),
            ),
        );
    }

    /**
     * @return Attribute<Provider, string>
     */
    protected function url(): Attribute
    {
        if (! $this->public) {
            return Attribute::make(
                get: fn () => null,
            );
        }

        $url = rtrim($this->pathPrefix . $this->path, '/');

        if ($this->site->trailing_slash) {
            $url .= '/';
        }

        return Attribute::make(
            get: fn () => $url,
        );
    }

    public function getParentKeyName()
    {
        return 'parent_ulid';
    }

    public function getLocalKeyName()
    {
        return 'ulid';
    }

    public function getPathName()
    {
        return 'parent_path';
    }

    /**
     * Custom depth column name.
     *
     * @see https://github.com/staudenmeir/laravel-adjacency-list/issues/87
     */
    public function getDepthName(): string
    {
        return 'backstage_depth';
    }

    /**
     * The full url, domain and language path. Without the content path, with trailing slash.
     *
     * @return Attribute<Provider, string>
     */
    protected function pathPrefix(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value, array $attributes) => self::getPathPrefixForLanguage($this->language_code, $this->site),
        );
    }

    /**
     * Calculate the path prefix for a given language code.
     * This is used in forms to update the path prefix live when language changes.
     */
    public static function getPathPrefixForLanguage(string $languageCode, ?Site $site = null): string
    {
        $url = '';

        if (! $site) {
            $site = Site::first();
        }

        if ($site) {
            $domain = $site->domains()->with([
                'languages' => function ($query) use ($languageCode) {
                    $query->where('code', $languageCode);
                    $query->limit(1);
                },
            ])
                ->where('environment', config('app.env'))
                ->first();

            if ($domain) {
                $url .= 'https://' . $domain->name;
                $url .= $site->path ? '/' . trim($site->path, '/') : '';
                if ($language = $domain->languages->first()) {
                    $url .= $language->pivot->path ? '/' . trim($language->pivot->path, '/') : '';
                }
            }
        }

        $url .= '/';

        return $url;
    }

    public function scopePublic($query): void
    {
        $query->where(function ($query) {
            return $query->where('public', true)
                ->where('published_at', '<=', now());
        });
    }

    public function blocks(string $field): array
    {
        return json_decode(
            json: $this->values->where('field.slug', $field)->first()?->value,
            associative: true
        ) ?? [];
    }

    /**
     * Returns the value of a field based on the slug. The following fields are available in Backstage:
     * checkbox (list),
     * Color
     * Date Time
     * File
     * Key Value
     * Markdown Editor
     * Radio
     * Repeater
     * Rich Editor
     * Select
     * Text
     * Textarea
     * Toggle
     * Uploadcare
     *
     * @see \Backstage\Models\ContentFieldValue::value()
     * @see https://docs.backstagephp.com/03-fields/01-introduction.html
     */
    public function field(string $slug): Content | HtmlString | Collection | array | bool | null
    {
        return $this->values->where('field.slug', $slug)->first()?->value();
    }

    public function rawField(string $field): mixed
    {
        return $this->values->where('field.slug', $field)->first()?->value;
    }

    public function view($data = [])
    {
        return View::first([$this->view, $this->type->template, 'types.default', 'backstage::types.default'], $data);
    }

    public function response(int $code = 200)
    {
        view()->share([
            'content' => $this,
        ]);

        return response($this->view(), $code);
    }

    public function translate(Language $language)
    {
        $existing = $this->existingTranslation($language);

        if ($existing) {
            return;
        }

        dispatch(new TranslateContent($this, $language));
    }

    public function existingTranslation(Language $language): ?self
    {
        return self::query()
            ->where('slug', $this->slug)
            ->where('type_slug', $this->type_slug)
            ->where('language_code', $language->code)
            ->first();
    }

    public function previewable(): bool
    {
        return $this->public
            && $this->published_at
            && $this->published_at <= now()
            && ($this->expired_at === null || $this->expired_at >= now());
    }
}
