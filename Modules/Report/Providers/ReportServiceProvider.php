<?php

namespace Modules\Report\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Core\Services\ModuleRegistry;
use Modules\Report\Models\ReportTemplate;
use Modules\Report\Policies\ReportTemplatePolicy;
use Modules\Report\Reports\AttendanceReportProvider;
use Modules\Report\Reports\FinanceOutstandingFeesProvider;
use Modules\Report\Reports\InvoiceReportProvider;
use Modules\Report\Reports\MeetingReportProvider;
use Modules\Report\Reports\StudentReportProvider;
use Modules\Report\Reports\StudyMaterialReportProvider;
use Modules\Report\Repositories\ReportGenerationRepository;
use Modules\Report\Repositories\ReportGenerationRepositoryInterface;
use Modules\Report\Repositories\ReportTemplateRepository;
use Modules\Report\Repositories\ReportTemplateRepositoryInterface;
use Modules\Report\Services\ReportRegistry;
use Nwidart\Modules\Support\ModuleServiceProvider;

class ReportServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Report';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'report';

    /**
     * Each entry pairs the module that owns the data with the provider that
     * reads it, so a provider disappears when its module is disabled.
     *
     * @var array<int, array{0: string, 1: class-string}>
     */
    private const REPORT_PROVIDERS = [
        ['Attendance', AttendanceReportProvider::class],
        ['Student', StudentReportProvider::class],
        ['Finance', FinanceOutstandingFeesProvider::class],
        ['Finance', InvoiceReportProvider::class],
        ['Meeting', MeetingReportProvider::class],
        ['StudyMaterial', StudyMaterialReportProvider::class],
    ];

    public function register(): void
    {
        parent::register();

        $this->app->bind(ReportTemplateRepositoryInterface::class, ReportTemplateRepository::class);
        $this->app->bind(ReportGenerationRepositoryInterface::class, ReportGenerationRepository::class);

        $this->app->singleton(ReportRegistry::class, function ($app): ReportRegistry {
            $registry = new ReportRegistry($app->make(ModuleRegistry::class));

            foreach (self::REPORT_PROVIDERS as [$module, $provider]) {
                $registry->register($app->make($provider), $module);
            }

            return $registry;
        });
    }

    public function boot(): void
    {
        parent::boot();

        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);

        Gate::policy(ReportTemplate::class, ReportTemplatePolicy::class);
    }

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
