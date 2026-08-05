<?php

namespace Modules\Classroom\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Classroom\Interfaces\Repositories\ClassroomRepositoryInterface;
use Modules\Classroom\Models\Classroom;
use Modules\Classroom\Policies\ClassroomPolicy;
use Modules\Classroom\Repositories\ClassroomRepository;

class ClassroomServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            ClassroomRepositoryInterface::class,
            ClassroomRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadTranslationsFrom(__DIR__ . '/../lang', 'classroom');
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');

        Gate::policy(Classroom::class, ClassroomPolicy::class);
    }
}
