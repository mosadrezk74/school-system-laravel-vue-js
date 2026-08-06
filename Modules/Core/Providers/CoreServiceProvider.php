<?php

namespace Modules\Core\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Core\Contracts\TranslationProviderInterface;
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
