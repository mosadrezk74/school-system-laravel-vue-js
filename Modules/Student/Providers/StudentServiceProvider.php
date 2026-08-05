<?php

namespace Modules\Student\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Student\Repositories\StudentRepository;
use Modules\Student\Repositories\StudentRepositoryInterface;

class StudentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            StudentRepositoryInterface::class,
            StudentRepository::class
        );
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}
