<?php

namespace Modules\Promotion\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Promotion\Interfaces\Repositories\PromotionRepositoryInterface;
use Modules\Promotion\Models\StudentPromotion;
use Modules\Promotion\Policies\PromotionPolicy;
use Modules\Promotion\Repositories\PromotionRepository;
use Nwidart\Modules\Support\ModuleServiceProvider;

class PromotionServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Promotion';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'promotion';

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

        $this->app->bind(PromotionRepositoryInterface::class, PromotionRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

        // The package only namespaces published lang files, so in-module ones
        // need binding explicitly for `promotion::` keys to resolve.
        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);

        Gate::policy(StudentPromotion::class, PromotionPolicy::class);
    }
}
