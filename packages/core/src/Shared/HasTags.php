<?php

namespace Backstage\Shared;

use Backstage\Models\Tag;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasTags
{
    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable', 'taggables', 'taggable_ulid', 'tag_ulid');
    }
}
