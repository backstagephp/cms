<?php

namespace Backstage\Observers;

use Backstage\Models\Content;
use Backstage\Redirects\Laravel\Events\UrlHasChanged;

class ContentUrlObserver
{
    public function saving(Content $content)
    {
        if (! $content->public && $content->path) {
            $content->path = null;
        }
    }

    public function saved(Content $content)
    {
        if (! $content->public || ! $content->isDirty('path')) {
            return;
        }

        $oldPath = $content->getOriginal('path');

        if (! $oldPath) {
            return;
        }

        $oldUrl = $this->constructUrlWithPath($content, $oldPath);

        event(new UrlHasChanged(
            oldUrl: $oldUrl,
            newUrl: $content->url,
            code: 301
        ));
    }

    private function constructUrlWithPath(Content $content, string $path): string
    {
        $pathPrefix = Content::getPathPrefixForLanguage($content->language_code, $content->site);

        $url = rtrim($pathPrefix . $path, '/');

        if ($content->site->trailing_slash) {
            $url .= '/';
        }

        return $url;
    }
}
