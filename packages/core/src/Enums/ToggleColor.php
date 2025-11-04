<?php

namespace Backstage\Enums;

use Backstage\Concerns\SerializableEnumArray;

enum ToggleColor: string
{
    use SerializableEnumArray;

    case DANGER = 'danger';
    case GRAY = 'gray';
    case INFO = 'info';
    case PRIMARY = 'primary';
    case SUCCESS = 'success';
    case WARNING = 'warning';
}
