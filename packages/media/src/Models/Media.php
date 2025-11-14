<?php

namespace Backstage\Media\Models;

use Backstage\Translations\Laravel\Contracts\TranslatesAttributes;
use Backstage\Translations\Laravel\Models\Concerns\HasTranslatableAttributes;
use Filament\Facades\Filament;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @property string $ulid
 * @property string $filename
 * @property string $path
 * @property string $mime_type
 * @property int $size
 * @property int|null $width
 * @property int|null $height
 * @property string|null $alt
 * @property array|null $metadata
 * @property int|null $uploaded_by
 * @property string|null $tenant_ulid
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string $humanReadableSize
 * @property-read string $src
 */
class Media extends Model implements TranslatesAttributes
{
    use HasTranslatableAttributes;
    use HasUlids;
    use SoftDeletes;

    protected $primaryKey = 'ulid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'width' => 'integer',
        'height' => 'integer',
        'size' => 'integer',
        'public' => 'boolean',
        'created_at' => 'datetime:d-m-Y H:i',
        'updated_at' => 'datetime:d-m-Y H:i',
        'metadata' => 'array',
        'alt' => 'string',
    ];

    protected $appends = [
        'humanReadableSize',
        'src',
    ];

    public function getTranslatableAttributes(): array
    {
        return [
            'alt',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    /**
     * Get all models this media is attached to.
     */
    public function models(): MorphToMany
    {
        return $this->morphedByMany(
            config('backstage.media.model_namespace', 'App\Models'),
            'model',
            'media_relationships',
            'media_ulid',
            'model_id'
        )->withPivot('position', 'meta');
    }

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $tenantRelationship = Config::get('backstage.media.tenant_relationship');
            $tenantModel = Config::get('backstage.media.tenant_model');

            if ($tenantRelationship && class_exists($tenantModel)) {
                $currentTenant = Filament::getTenant();

                if ($currentTenant && property_exists($currentTenant, 'ulid')) {
                    $model->{$tenantRelationship . '_ulid'} = $currentTenant->ulid;
                }
            }
        });
    }

    public function user(): ?BelongsTo
    {
        return $this->belongsTo(Config::get('backstage.media.user_model'), 'uploaded_by');
    }

    public function tenant(): ?BelongsTo
    {
        $tenantRelationship = Config::get('backstage.media.tenant_relationship');
        $tenantModel = Config::get('backstage.media.tenant_model');

        if ($tenantRelationship && class_exists($tenantModel)) {
            return $this->belongsTo(
                $tenantModel,
                $tenantRelationship . '_ulid'
            );
        }

        return null;
    }

    public function getHumanReadableSizeAttribute(): string
    {
        $bytes = $this->size;

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getSrcAttribute(): string
    {
        $disk = Config::get('backstage.media.disk', 'public');
        $directory = Config::get('backstage.media.directory', 'media');

        return Storage::disk($disk)->url($directory . '/' . $this->filename);
    }

    public function download(): StreamedResponse
    {
        $disk = Config::get('backstage.media.disk', 'public');
        $directory = Config::get('backstage.media.directory', 'media');

        return Storage::disk($disk)->download($directory . '/' . $this->filename);
    }
}
