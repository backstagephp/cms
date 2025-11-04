<?php

namespace Backstage\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Backstage\Backstage
 */
class Backstage extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Backstage\Backstage::class;
    }
}
