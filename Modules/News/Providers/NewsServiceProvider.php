<?php

namespace Modules\News\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\News\Interfaces\Repositories\NewsRepositoryInterface;
use Modules\News\Models\News;
use Modules\News\Policies\NewsPolicy;
use Modules\News\Repositories\NewsRepository;
use Nwidart\Modules\Support\ModuleServiceProvider;

class NewsServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'News';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'news';

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

        $this->app->bind(NewsRepositoryInterface::class, NewsRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

        // The package only namespaces published lang files, so in-module ones
        // need binding explicitly for `news::` keys to resolve.
        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);

        Gate::policy(News::class, NewsPolicy::class);
    }
}
