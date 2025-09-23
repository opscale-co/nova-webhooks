<?php

namespace Opscale\NovaWebhooks\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool as NovaTool;
use Opscale\NovaWebhooks\Tool;

class Authorize
{
    final public function handle(Request $request, Closure $next): Response
    {
        /** @var Collection<int, NovaTool> $tools */
        $tools = Collection::make(Nova::registeredTools());

        /** @var Tool|null $tool */
        $tool = $tools->first([$this, 'matchesTool']);

        if ($tool !== null && $tool->authorize($request)) {
            return $next($request);
        }

        abort(403);
    }

    final public function matchesTool(object $tool): bool
    {
        return $tool instanceof Tool;
    }
}
