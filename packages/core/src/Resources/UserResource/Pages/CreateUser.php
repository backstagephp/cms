<?php

namespace Backstage\Resources\UserResource\Pages;

use Backstage\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
