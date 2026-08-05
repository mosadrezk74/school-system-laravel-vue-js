<?php

namespace Modules\GuardianProfile\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\GuardianProfile\Models\GuardianProfile;
use Modules\GuardianProfile\Policies\GuardianPolicy;
use Modules\GuardianProfile\Repositories\GuardianRepository;
use Modules\GuardianProfile\Repositories\GuardianRepositoryInterface;
use Nwidart\Modules\Support\ModuleServiceProvider;

class GuardianProfileServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'GuardianProfile';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'guardianprofile';

    public function register(): void
    {
        parent::register();

        $this->app->bind(GuardianRepositoryInterface::class, GuardianRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

        // The package only namespaces published lang files, so in-module ones
        // need binding explicitly for `guardian::` keys to resolve.
        $this->loadTranslationsFrom(module_path($this->name, 'lang'), 'guardian');

        Gate::policy(GuardianProfile::class, GuardianPolicy::class);
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
