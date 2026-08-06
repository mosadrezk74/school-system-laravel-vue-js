<?php

namespace Modules\Examination\Providers;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Gate;
use Modules\Examination\Models\Exam;
use Modules\Examination\Models\ExamGrade;
use Modules\Examination\Policies\ExamGradePolicy;
use Modules\Examination\Policies\ExamPolicy;
use Modules\Examination\Repositories\ExamGradeRepository;
use Modules\Examination\Repositories\ExamGradeRepositoryInterface;
use Modules\Examination\Repositories\ExamRepository;
use Modules\Examination\Repositories\ExamRepositoryInterface;
use Modules\Examination\Repositories\ExamResultRepository;
use Modules\Examination\Repositories\ExamResultRepositoryInterface;
use Modules\Examination\Repositories\ExamSubjectRepository;
use Modules\Examination\Repositories\ExamSubjectRepositoryInterface;
use Nwidart\Modules\Support\ModuleServiceProvider;

class ExaminationServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Examination';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'examination';

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

    public function register(): void
    {
        parent::register();

        $this->app->bind(ExamRepositoryInterface::class, ExamRepository::class);
        $this->app->bind(ExamSubjectRepositoryInterface::class, ExamSubjectRepository::class);
        $this->app->bind(ExamResultRepositoryInterface::class, ExamResultRepository::class);
        $this->app->bind(ExamGradeRepositoryInterface::class, ExamGradeRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

        // The package only namespaces published lang files, so in-module ones
        // need binding explicitly for `examination::` keys to resolve.
        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);

        Gate::policy(Exam::class, ExamPolicy::class);
        Gate::policy(ExamGrade::class, ExamGradePolicy::class);
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
