<?php

namespace Backstage\Redirects\Laravel\Listeners;

use Backstage\Redirects\Laravel\Events\UrlHasChanged;
use Backstage\Redirects\Laravel\Models\Redirect;

class RedirectOldUrlToNewUrl
{
    public function handle(UrlHasChanged $event): void
    {
        Redirect::create([
            'source' => $event->oldUrl,
            'destination' => $event->newUrl,
            'code' => $event->code,
        ]);
    }
}
