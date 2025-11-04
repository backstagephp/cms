<?php

namespace Backstage\Observers;

use Backstage\Models\Content;

class ContentDepthObserver
{
    public function saved(Content $content)
    {
        if ($content->isDirty('parent_ulid')) {
            dispatch(function () use ($content) {
                $parentContent = Content::tree()->depthFirst()->where('ulid', $content->parent_ulid)->first();
                $content->depth = $parentContent ? $parentContent->depth : 0;
                $content->saveQuietly();
            });
        }
    }
}
