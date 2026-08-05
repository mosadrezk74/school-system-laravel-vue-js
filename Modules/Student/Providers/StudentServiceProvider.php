<?php

namespace Modules\Student\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Student\Models\StudentProfile;
use Modules\Student\Policies\StudentPolicy;
use Modules\Student\Repositories\StudentRepository;
use Modules\Student\Repositories\StudentRepositoryInterface;
use Nwidart\Modules\Support\ModuleServiceProvider;

class StudentServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Student';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'student';

    public function register(): void
    {
        parent::register();

        $this->app->bind(StudentRepositoryInterface::class, StudentRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

        // The package only namespaces published lang files, so in-module ones
        // need binding explicitly for `student::` keys to resolve.
        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);

        Gate::policy(StudentProfile::class, StudentPolicy::class);
    }

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
