<?php

namespace Modules\Core\Abstracts;

use Illuminate\Http\RedirectResponse;
use Inertia\Response;

/**
 * Base for controllers that render Vue pages.
 *
 * Pair with BaseApiController, which is for endpoints returning JSON:
 *   full UI page (Index/Create/Edit) → BaseInertiaController
 *   JSON endpoint (mobile, polling)  → BaseApiController
 */
abstract class BaseInertiaController extends BaseController
{
    /**
     * @param  array<string, mixed>  $props
     */
    protected function render(string $component, array $props = []): Response
    {
        return inertia($component, $props);
    }

    /** Redirect back with a success flash the layout turns into a toast. */
    protected function backWithSuccess(string $message): RedirectResponse
    {
        return back()->with('success', $message);
    }

    protected function backWithError(string $message): RedirectResponse
    {
        return back()->with('error', $message);
    }

    /**
     * @param  array<string, mixed>  $parameters
     */
    protected function toRoute(string $route, array $parameters = [], ?string $message = null): RedirectResponse
    {
        $redirect = redirect()->route($route, $parameters);

        return $message ? $redirect->with('success', $message) : $redirect;
    }
}
