<?php

namespace Modules\Core\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Modules\Core\Contracts\TranslationProviderInterface;
use Modules\Core\Models\Activity;
use Modules\Core\Models\ErrorReport;
use Modules\Core\Policies\ActivityPolicy;
use Modules\Core\Policies\ErrorReportPolicy;
use Modules\Core\Services\Translation\LibreTranslateProvider;

class CoreServiceProvider extends ServiceProvider
{
    /*
     * Container bindings for BaseRepository/BaseService were removed: both are
     * abstract with required constructor arguments, so resolving either would
     * have fataled. Modules bind their own concrete repositories instead.
     */
    public function register(): void
    {
        $this->app->bind(TranslationProviderInterface::class, LibreTranslateProvider::class);

    }

    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'core');
        $this->loadMigrationsFrom(__DIR__.'/../Database/Migrations');

        Gate::policy(ErrorReport::class, ErrorReportPolicy::class);
        Gate::policy(Activity::class, ActivityPolicy::class);

        /*
         * Lets the sidebar hide an entry the user could not open anyway. The
         * route's policy check is what actually protects it — this only keeps
         * a dead link out of the menu.
         */
        Inertia::share([
            'can' => fn () => [
                'viewErrorReports' => auth()->user()?->can('error-report.view') ?? false,
                'viewActivity' => auth()->user()?->can('activity.view') ?? false,
                'viewRoles' => auth()->user()?->can('role.view') ?? false,
                'viewUsers' => auth()->user()?->can('user.view') ?? false,
                'viewSchoolTerms' => auth()->user()?->can('school-term.view') ?? false,
                'viewPromotions' => auth()->user()?->can('student-promotion.view') ?? false,
                'viewNews' => auth()->user()?->can('news.view') ?? false,
            ],
        ]);

        /*
         * Reading a stack trace is not tied to a single report, so it is a
         * class-level ability rather than a per-model one.
         */
        Gate::define('viewTrace', [ErrorReportPolicy::class, 'viewTrace']);

        $this->registerModulePageNamespaces();
    }

    /**
     * Lets every module keep its Vue pages beside its PHP.
     *
     * A controller renders "Student::Students/Index" and Inertia resolves it
     * to Modules/Student/resources/js/Pages/Students/Index.vue. Registering
     * them centrally means a new module needs no provider boilerplate.
     *
     * Deferred via afterResolving because inertia.view-finder is bound after
     * service providers boot.
     */
    private function registerModulePageNamespaces(): void
    {
        $this->app->afterResolving('inertia.view-finder', function ($finder): void {
            foreach (glob(base_path('Modules/*/resources/js/Pages'), GLOB_ONLYDIR) ?: [] as $path) {
                $module = basename(dirname($path, 3));
                $finder->addNamespace($module, $path);
            }
        });
    }
}
