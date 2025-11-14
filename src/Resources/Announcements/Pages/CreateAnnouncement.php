<?php

namespace Backstage\Announcements\Resources\Announcements\Pages;

use Backstage\Announcements\Resources\Announcements\AnnouncementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAnnouncement extends CreateRecord
{
    protected static string $resource = AnnouncementResource::class;
}
