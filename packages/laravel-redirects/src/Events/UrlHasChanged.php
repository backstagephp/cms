<?php

namespace Backstage\Redirects\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UrlHasChanged
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly string $oldUrl,
        public readonly string $newUrl,
        public readonly int $code = 301
    ) {}
}
