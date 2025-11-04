<?php

namespace Backstage\Models;

use Backstage\Fields\Concerns\HasFields;
use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\HtmlString;

/**
 * @property string $ulid
 * @property string $slug
 * @property string $name
 * @property string $name_field
 * @property string $icon
 * @property string $component
 */
class Block extends Model
{
    use HasFields;
    use HasPackageFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }

    public function render(): HtmlString
    {
        return new Htmlstring(
            Blade::render("<x-{$this->slug} :attributes='' />")
        );
    }
}
