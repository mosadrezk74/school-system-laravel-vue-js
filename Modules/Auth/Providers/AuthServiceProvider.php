<?php

namespace Modules\Auth\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Auth\Repositories\ProfileRepository;
use Modules\Auth\Repositories\ProfileRepositoryInterface;
use Nwidart\Modules\Support\ModuleServiceProvider;

class AuthServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Auth';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'auth';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    public function register(): void
    {
        parent::register();

        $this->app->bind(ProfileRepositoryInterface::class, ProfileRepository::class);
    }

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    public function boot(): void
    {
        parent::boot();

        // The package's registerTranslations() only namespaces published
        // lang files, so in-module ones need binding explicitly.
        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
    }

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

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
