<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Cache;
use Modules\Core\Exceptions\BaseException;
use Nwidart\Modules\Facades\Module;

class ModuleRegistry
{
    public const CACHE_KEY = 'modules.enabled';

    private const FOUNDATIONAL = ['Core', 'Auth', 'Setting', 'Academic'];

    public function isFoundational(string $name): bool
    {
        return in_array($name, self::FOUNDATIONAL, true);
    }

    /**
     * @return array<int, string>
     */
    public function enabledNames(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => collect(Module::all())
            ->filter(fn ($module) => $module->isEnabled())
            ->map(fn ($module) => $module->getName())
            ->values()
            ->all());
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * @return array<int, array{name: string, description: string, icon: string, navGroup: string, canBeDisabled: bool, enabled: bool}>
     */
    public function all(): array
    {
        return collect(Module::all())
            ->map(fn ($module) => [
                'name' => $module->getName(),
                'description' => $module->getDescription(),
                'icon' => $module->get('icon', 'bx-package'),
                'navGroup' => $module->get('navGroup', $module->getName()),
                'canBeDisabled' => ! $this->isFoundational($module->getName()),
                'enabled' => $module->isEnabled(),
            ])
            ->sortBy('name')
            ->values()
            ->all();
    }

    public function isEnabled(string $name): bool
    {
        return (bool) Module::find($name)?->isEnabled();
    }

    public function enable(string $name): void
    {
        Module::find($name)?->enable();

        self::forgetCache();
    }

    public function disable(string $name): void
    {
        if ($this->isFoundational($name)) {
            throw BaseException::badRequest(__('setting::onboarding.cannot_disable_foundational'));
        }

        Module::find($name)?->disable();

        self::forgetCache();
    }
}
