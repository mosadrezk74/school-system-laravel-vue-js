<?php

namespace Modules\Meeting\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Modules\Meeting\Models\Meeting;
use Modules\Meeting\Policies\MeetingPolicy;
use Modules\Meeting\Repositories\MeetingRepository;
use Modules\Meeting\Repositories\MeetingRepositoryInterface;
use Modules\Timetable\Models\TimetableEntry;
use Nwidart\Modules\Support\ModuleServiceProvider;

class MeetingServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Meeting';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'meeting';

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

        $this->app->bind(MeetingRepositoryInterface::class, MeetingRepository::class);
    }

    public function boot(): void
    {
        parent::boot();

        $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);

        Gate::policy(Meeting::class, MeetingPolicy::class);

        Gate::define('meeting.startForEntry', function (User $user, TimetableEntry $entry): bool {
            return app(MeetingPolicy::class)->startForEntry($user, $entry);
        });
    }
}
