<?php

namespace Backstage\Models\Concerns;

use Backstage\Models\Content;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

trait HasContentRelations
{
    public function content(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'relationables', 'relation_ulid', 'related_ulid')
            ->where('relationables.relation_type', 'content')
            ->where('relationables.related_type', $this->getMorphClass());
    }

    public function relatedContent(): BelongsToMany
    {
        return $this->belongsToMany(Content::class, 'relationables', 'related_ulid', 'relation_ulid')
            ->where('relationables.related_type', 'content')
            ->where('relationables.relation_type', $this->getMorphClass());
    }
}
