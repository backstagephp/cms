<?php

namespace Backstage\Models;

use Backstage\Shared\HasPackageFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Language extends Model
{
    use HasPackageFactory;

    public function domains(): BelongsToMany
    {
        return $this->belongsToMany(Domain::class, 'domain_language', 'language_code', 'domain_ulid')
            ->withPivot('path');
    }
}
