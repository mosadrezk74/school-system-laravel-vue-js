<?php

namespace Modules\Setting\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Modules\Core\Services\ModuleRegistry;
use Modules\Setting\Models\Language;
use Modules\Setting\Models\SchoolSetting;
use Modules\Setting\Policies\LanguagePolicy;
use Modules\Setting\Policies\SchoolSettingPolicy;
use Modules\Setting\Services\LanguageService;
use Modules\Setting\Services\SettingsService;
use Nwidart\Modules\Support\ModuleServiceProvider;

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
        Gate::policy(SchoolSetting::class, SchoolSettingPolicy::class);

        // The frontend switcher renders exactly what the school has enabled,
        // so adding or removing a language is a database change with no
        // rebuild and no hard-coded locale list in the JS.
        Inertia::share([
            'locale' => fn () => app()->getLocale(),
            'direction' => fn () => app(LanguageService::class)->directionFor(app()->getLocale()),
            'languages' => fn () => app(LanguageService::class)->active()->values()->all(),
            'setupCompleted' => fn () => app(SettingsService::class)->isSetupCompleted(),
            'enabledModules' => fn () => app(ModuleRegistry::class)->enabledNames(),
        ]);
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
