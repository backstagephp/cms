<?php

namespace Backstage\Models;

use Backstage\Fields\Concerns\HasFields;
use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Type extends Model
{
    use HasFields;
    use HasPackageFactory;

    protected $primaryKey = 'slug';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'og_image_fields' => 'array',
            'parent_filters' => 'array',
        ];
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }

    public function getTemplateAttribute(): string
    {
        return 'types.' . $this->slug;
    }

    public function getTemplatePathAttribute(): string
    {
        return resource_path('views/types/' . $this->slug . '.blade.php');
    }
}
