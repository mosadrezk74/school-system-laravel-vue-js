<?php

namespace Modules\Assignment\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Assignment\Models\Assignment;
use Modules\Assignment\Models\AssignmentSubmission;
use Modules\Assignment\Policies\AssignmentPolicy;
use Modules\Assignment\Policies\AssignmentSubmissionPolicy;
use Modules\Assignment\Repositories\AssignmentRepository;
use Modules\Assignment\Repositories\AssignmentRepositoryInterface;
use Modules\Assignment\Repositories\AssignmentSubmissionRepository;
use Modules\Assignment\Repositories\AssignmentSubmissionRepositoryInterface;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AssignmentServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Assignment';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'assignment';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(AssignmentRepositoryInterface::class, AssignmentRepository::class);
        $this->app->bind(AssignmentSubmissionRepositoryInterface::class, AssignmentSubmissionRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

        // The package only namespaces published lang files, so in-module ones
        // need binding explicitly for `assignment::` keys to resolve.
        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);

        Gate::policy(Assignment::class, AssignmentPolicy::class);
        Gate::policy(AssignmentSubmission::class, AssignmentSubmissionPolicy::class);
    }

    /**
     * Define module schedules.
     *
     * @param  $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
