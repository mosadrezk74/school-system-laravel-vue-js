<?php

namespace Modules\Academic\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Academic\Interfaces\Repositories\GradeLevelRepositoryInterface;
use Modules\Academic\Interfaces\Repositories\SemesterRepositoryInterface;
use Modules\Academic\Models\AcademicYear;
use Modules\Academic\Models\GradeLevel;
use Modules\Academic\Models\Semester;
use Modules\Academic\Observers\AcademicYearObserver;
use Modules\Academic\Observers\SemesterObserver;
use Modules\Academic\Policies\GradeLevelPolicy;
use Modules\Academic\Policies\SemesterPolicy;
use Modules\Academic\Repositories\GradeLevelRepository;
use Modules\Academic\Repositories\SemesterRepository;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AcademicServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Academic';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'academic';

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

    /**
     * The package's registerTranslations() only binds the `academic::`
     * namespace when translations are published to resources/lang/modules.
     * For in-module lang files it calls loadTranslationsFrom() without a
     * namespace, so `academic::...` keys render as their own key. Bind it here.
     */
    public function register(): void
    {
        parent::register();

        $this->app->bind(SemesterRepositoryInterface::class, SemesterRepository::class);
        $this->app->bind(GradeLevelRepositoryInterface::class, GradeLevelRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

        $this->loadTranslationsFrom(
            module_path($this->name, 'lang'),
            $this->nameLower,
        );

        Gate::policy(Semester::class, SemesterPolicy::class);
        Gate::policy(GradeLevel::class, GradeLevelPolicy::class);

        AcademicYear::observe(AcademicYearObserver::class);
        Semester::observe(SemesterObserver::class);
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
