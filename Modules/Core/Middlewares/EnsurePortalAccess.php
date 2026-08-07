<?php

namespace Modules\Core\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Services\PortalRouter;
use Symfony\Component\HttpFoundation\Response;

class EnsurePortalAccess
{
    public function __construct(private readonly PortalRouter $router) {}

    public function handle(Request $request, Closure $next, string $portal): Response
    {
        if ($this->router->portalFor($request->user()) !== $portal) {
            abort(403);
        }

        return $next($request);
    }
}
