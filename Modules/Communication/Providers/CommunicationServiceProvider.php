<?php

namespace Modules\Communication\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\Communication\Models\BroadcastMessage;
use Modules\Communication\Policies\MessagePolicy;
use Modules\Communication\Repositories\MessageRepository;
use Modules\Communication\Repositories\MessageRepositoryInterface;
use Nwidart\Modules\Support\ModuleServiceProvider;

class CommunicationServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Communication';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'communication';

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

        $this->app->bind(MessageRepositoryInterface::class, MessageRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);

        Gate::policy(BroadcastMessage::class, MessagePolicy::class);
    }
}
