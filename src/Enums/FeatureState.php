<?php

namespace Codinglabs\FeatureFlags\Enums;

enum FeatureState: string
{
    case ON = 'on';
    case OFF = 'off';
    case DYNAMIC = 'dynamic';

    public static function on(): self
    {
        return self::ON;
    }

    public static function off(): self
    {
        return self::OFF;
    }

    public static function dynamic(): self
    {
        return self::DYNAMIC;
    }
}
