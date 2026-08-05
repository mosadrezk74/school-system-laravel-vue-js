<?php

namespace Modules\Core\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ParseMultipartPut
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethod('PUT') || $request->isMethod('PATCH')) {
            if (str_contains($request->header('Content-Type'), 'multipart/form-data')) {
                parse_str($request->getContent(), $result);
                $request->merge($result);
            }
        }

        return $next($request);
    }
}
