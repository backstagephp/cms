<?php

namespace Backstage\Observers;

use Backstage\Models\Content;

class ContentRevisionObserver
{
    public function updating(Content $content)
    {
        $content->versions()->create([
            'data' => [
                'fields' => $content->getFormattedFieldValues(),
                'meta_tags' => $content->getOriginal('meta_tags'),
            ],
        ]);
    }
}
