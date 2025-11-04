<?php

namespace Backstage\Models;

use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use HasPackageFactory;
    use HasUlids;

    protected $primaryKey = 'ulid';

    protected $table = 'media';

    protected $guarded = [];

    protected function casts(): array
    {
        return [];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
