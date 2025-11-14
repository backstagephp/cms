<?php

namespace Backstage\Models;

use Backstage\Media\Models\Media as BaseMedia;
use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends BaseMedia
{
    use HasPackageFactory;

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }
}
