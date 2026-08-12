<?php

namespace Modules\Auth\Providers;

use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Auth\Interfaces\Repositories\AdminRepositoryInterface;
use Modules\Auth\Interfaces\Repositories\RoleRepositoryInterface;
use Modules\Auth\Models\Role;
use Modules\Auth\Policies\AdminPolicy;
use Modules\Auth\Policies\RolePolicy;
use Modules\Auth\Repositories\AdminRepository;
use Modules\Auth\Repositories\ProfileRepository;
use Modules\Auth\Repositories\ProfileRepositoryInterface;
use Modules\Auth\Repositories\RoleRepository;
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
        $this->app->bind(RoleRepositoryInterface::class, RoleRepository::class);
        $this->app->bind(AdminRepositoryInterface::class, AdminRepository::class);
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

        Gate::policy(Role::class, RolePolicy::class);

        // User had no policy before; the portal authorises through the profile
        // models (StudentPolicy and friends), never through a User gate.
        Gate::policy(User::class, AdminPolicy::class);
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
