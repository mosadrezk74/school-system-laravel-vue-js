<?php

namespace Modules\Portal\Providers;

use Modules\Portal\Services\PortalScopeService;
use Nwidart\Modules\Support\ModuleServiceProvider;

class PortalServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Portal';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'portal';

    public function register(): void
    {
        parent::register();

        // Scoped rather than singleton: the memoised ownership sets belong to
        // one signed-in user, and a worker serving several requests must not
        // carry one user's accessible ids into the next.
        $this->app->scoped(PortalScopeService::class);
    }

    public function boot(): void
    {
        parent::boot();

        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
    }

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
