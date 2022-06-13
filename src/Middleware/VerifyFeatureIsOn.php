<?php

namespace Codinglabs\FeatureFlags\Middleware;

use Closure;
use Illuminate\Http\Request;
use Codinglabs\FeatureFlags\Facades\FeatureFlag;

class VerifyFeatureIsOn
{
    public function handle(Request $request, Closure $next, $feature)
    {
        abort_unless(FeatureFlag::isOn($feature), 404, __('Page not found.'));

        return $next($request);
    }
}
