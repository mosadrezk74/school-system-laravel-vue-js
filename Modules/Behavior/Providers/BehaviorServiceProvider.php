<?php

namespace Modules\Behavior\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Behavior\Models\BehaviorCategory;
use Modules\Behavior\Models\BehaviorRecord;
use Modules\Behavior\Policies\BehaviorCategoryPolicy;
use Modules\Behavior\Policies\BehaviorRecordPolicy;
use Modules\Behavior\Repositories\BehaviorRecordRepository;
use Modules\Behavior\Repositories\BehaviorRecordRepositoryInterface;
use Nwidart\Modules\Support\ModuleServiceProvider;

class BehaviorServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Behavior';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'behavior';

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

        $this->app->bind(BehaviorRecordRepositoryInterface::class, BehaviorRecordRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

        // The package only namespaces published lang files, so in-module ones
        // need binding explicitly for `behavior::` keys to resolve.
        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);

        Gate::policy(BehaviorRecord::class, BehaviorRecordPolicy::class);
        Gate::policy(BehaviorCategory::class, BehaviorCategoryPolicy::class);
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
