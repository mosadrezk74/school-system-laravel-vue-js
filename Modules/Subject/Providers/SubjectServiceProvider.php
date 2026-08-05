<?php

namespace Modules\Subject\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Subject\Interfaces\Repositories\SubjectRepositoryInterface;
use Modules\Subject\Models\Subject;
use Modules\Subject\Policies\SubjectPolicy;
use Modules\Subject\Repositories\SubjectRepository;

class SubjectServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            SubjectRepositoryInterface::class,
            SubjectRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'subject');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Gate::policy(Subject::class, SubjectPolicy::class);
    }
}
