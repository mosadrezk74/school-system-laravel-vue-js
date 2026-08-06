<?php

namespace Modules\Timetable\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Timetable\Models\Period;
use Modules\Timetable\Models\Timetable;
use Modules\Timetable\Policies\PeriodPolicy;
use Modules\Timetable\Policies\TimetablePolicy;
use Modules\Timetable\Repositories\PeriodRepository;
use Modules\Timetable\Repositories\PeriodRepositoryInterface;
use Modules\Timetable\Repositories\TimetableEntryRepository;
use Modules\Timetable\Repositories\TimetableEntryRepositoryInterface;
use Modules\Timetable\Repositories\TimetableRepository;
use Modules\Timetable\Repositories\TimetableRepositoryInterface;
use Modules\Timetable\Repositories\TimetableSessionRepository;
use Modules\Timetable\Repositories\TimetableSessionRepositoryInterface;
use Nwidart\Modules\Support\ModuleServiceProvider;

class TimetableServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Timetable';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'timetable';

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

        $this->app->bind(PeriodRepositoryInterface::class, PeriodRepository::class);
        $this->app->bind(TimetableRepositoryInterface::class, TimetableRepository::class);
        $this->app->bind(TimetableEntryRepositoryInterface::class, TimetableEntryRepository::class);
        $this->app->bind(TimetableSessionRepositoryInterface::class, TimetableSessionRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

        // The package only namespaces published lang files, so in-module ones
        // need binding explicitly for `timetable::` keys to resolve.
        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);

        Gate::policy(Period::class, PeriodPolicy::class);
        Gate::policy(Timetable::class, TimetablePolicy::class);
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
