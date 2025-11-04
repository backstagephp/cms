<?php

namespace Backstage\Resources;

use Backstage\Redirects\Filament\Resources\RedirectResource as BaseResource;
use Backstage\Resources\ContentResource\Pages\ListContentMetaTags;

class RedirectResource extends BaseResource
{
    protected static ?string $tenantOwnershipRelationshipName = 'site';

    public static function getNavigationGroup(): ?string
    {
        $dedicatedGroup = ListContentMetaTags::getNavigationGroup();

        return $dedicatedGroup;
    }
}
