<?php

namespace Modules\Notification\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Modules\Notification\Repositories\NotificationRepository;
use Modules\Notification\Repositories\NotificationRepositoryInterface;
use Nwidart\Modules\Support\ModuleServiceProvider;

class NotificationServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Notification';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'notification';

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
    public function register(): void
    {
        parent::register();

        $this->app->bind(NotificationRepositoryInterface::class, NotificationRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

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
