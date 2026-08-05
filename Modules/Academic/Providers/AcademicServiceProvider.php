<?php

namespace Modules\Academic\Providers;

use Illuminate\Console\Scheduling\Schedule;
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
    public function boot(): void
    {
        parent::boot();

        $this->loadTranslationsFrom(
            module_path($this->name, 'lang'),
            $this->nameLower,
        );

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
