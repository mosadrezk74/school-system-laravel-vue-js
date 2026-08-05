<?php

namespace Modules\Teacher\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Modules\Teacher\Interfaces\Repositories\TeacherRepositoryInterface;
use Modules\Teacher\Models\TeacherProfile;
use Modules\Teacher\Policies\TeacherPolicy;
use Modules\Teacher\Repositories\TeacherRepository;

class TeacherServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            TeacherRepositoryInterface::class,
            TeacherRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'teacher');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        Gate::policy(TeacherProfile::class, TeacherPolicy::class);
    }
}
