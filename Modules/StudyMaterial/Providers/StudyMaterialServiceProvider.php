<?php

namespace Modules\StudyMaterial\Providers;

use Illuminate\Support\Facades\Gate;
use Modules\StudyMaterial\Models\StudyResource;
use Modules\StudyMaterial\Policies\StudyResourcePolicy;
use Modules\StudyMaterial\Repositories\StudyResourceRepository;
use Modules\StudyMaterial\Repositories\StudyResourceRepositoryInterface;
use Nwidart\Modules\Support\ModuleServiceProvider;

class StudyMaterialServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'StudyMaterial';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'studymaterial';

    public function register(): void
    {
        parent::register();

        $this->app->bind(StudyResourceRepositoryInterface::class, StudyResourceRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);

        Gate::policy(StudyResource::class, StudyResourcePolicy::class);
    }

    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];
}
