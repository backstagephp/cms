<?php

namespace Backstage\Announcements\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Backstage\Announcements\Announcements
 *
 * @method static register()
 */
class Announcements extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Backstage\Announcements\Announcements::class;
    }
}
