<?php

namespace Modules\Teacher\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Teacher\Interfaces\Repositories\TeacherRepositoryInterface;
use Modules\Teacher\Models\TeacherProfile;
use Modules\Teacher\Policies\TeacherPolicy;
use Modules\Teacher\Repositories\TeacherRepository;
use Nwidart\Modules\Support\ModuleServiceProvider;

class TeacherServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Teacher';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'teacher';

    public function register(): void
    {
        parent::register();

        $this->app->bind(TeacherRepositoryInterface::class, TeacherRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

        // The package only namespaces published lang files, so in-module ones
        // need binding explicitly for `teacher::` keys to resolve.
        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);

        Gate::policy(TeacherProfile::class, TeacherPolicy::class);
    }

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
