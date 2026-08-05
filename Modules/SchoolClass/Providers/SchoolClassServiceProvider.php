<?php

namespace Modules\SchoolClass\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\SchoolClass\Models\SchoolClass;
use Modules\SchoolClass\Policies\ClassPolicy;
use Modules\SchoolClass\Repositories\ClassRepository;
use Modules\SchoolClass\Repositories\ClassRepositoryInterface;
use Nwidart\Modules\Support\ModuleServiceProvider;

class SchoolClassServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'SchoolClass';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'schoolclass';

    public function register(): void
    {
        parent::register();

        $this->app->bind(ClassRepositoryInterface::class, ClassRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

        // The package only namespaces published lang files, so in-module ones
        // need binding explicitly for `school-class::` keys to resolve.
        $this->loadTranslationsFrom(module_path($this->name, 'lang'), 'school-class');

        Gate::policy(SchoolClass::class, ClassPolicy::class);
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
