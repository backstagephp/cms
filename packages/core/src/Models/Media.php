<?php

namespace Backstage\Models;

use Backstage\Shared\HasPackageFactory;
use Backstage\Translations\Laravel\Contracts\TranslatesAttributes;
use Backstage\Translations\Laravel\Models\Concerns\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model implements TranslatesAttributes
{
    use HasPackageFactory;
    use HasTranslatableAttributes;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $table = 'media';

    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'alt' => 'string',
        ];
    }

    public function getTranslatableAttributes(): array
    {
        return [
            'alt',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
