<?php

namespace Modules\Core\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Modules\Core\Services\ModuleRegistry;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function __construct(private readonly ModuleRegistry $registry) {}

    public function handle(Request $request, Closure $next, string $module): Response
    {
        $this->registry->requireEnabled($module);

        return $next($request);
    }
}
