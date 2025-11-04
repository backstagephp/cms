<?php

namespace Backstage\Enums;

use Backstage\Concerns\SerializableEnumArray;

enum ColorFormat: string
{
    use SerializableEnumArray;

    case HEX = 'hex';
    case RGB = 'rgb';
    case RGBA = 'rgba';
    case HSL = 'hsl';
}
