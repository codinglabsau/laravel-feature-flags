<?php

namespace Codinglabs\FeatureFlags\Tests\Fixtures;

enum TestScope: string
{
    case Development = 'development';
    case Release = 'release';
}
