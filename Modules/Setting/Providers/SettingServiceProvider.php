<?php

namespace Modules\Setting\Providers;

use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Modules\Setting\Models\Language;
use Modules\Setting\Policies\LanguagePolicy;
use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

class SettingServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Setting';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'setting';

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
    public function boot(): void
    {
        parent::boot();

        // The package only namespaces published lang files, so in-module ones
        // need binding explicitly for `setting::` keys to resolve.
        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);

        Gate::policy(Language::class, LanguagePolicy::class);

        // The frontend switcher renders exactly what the school has enabled,
        // so adding or removing a language is a database change with no
        // rebuild and no hard-coded locale list in the JS.
        Inertia::share([
            'locale' => fn () => app()->getLocale(),
            'direction' => fn () => Language::directionFor(app()->getLocale()),
            'languages' => fn () => Language::active()->values()->all(),
        ]);
    }

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
