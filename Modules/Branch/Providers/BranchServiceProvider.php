<?php

namespace Modules\Branch\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Branch\Interfaces\Repositories\BranchRepositoryInterface;
use Modules\Branch\Models\Branch;
use Modules\Branch\Policies\BranchPolicy;
use Modules\Branch\Repositories\BranchRepository;

class BranchServiceProvider extends ServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Branch';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'branch';

    public function register(): void
    {
        // Register repository binding
        $this->app->bind(
            BranchRepositoryInterface::class,
            BranchRepository::class
        );
    }

    public function boot(): void
    {
        // Load routes - THIS WAS MISSING!
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        
        // Load translations
        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
        
        // Load migrations
        $this->loadMigrationsFrom(module_path($this->name, 'Database/Migrations'));
        
        // Register policies
        Gate::policy(Branch::class, BranchPolicy::class);
    }
}