<?php

namespace Modules\Core\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Modules\Core\Exceptions\BaseException;
use Nwidart\Modules\Facades\Module;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ModuleRegistry
{
    public const CACHE_KEY = 'modules.statuses';

    private const FOUNDATIONAL = ['Core', 'Auth', 'Setting', 'Academic'];

    /** @var array<string, bool>|null */
    private ?array $statuses = null;

    public function isFoundational(string $name): bool
    {
        return in_array($name, self::FOUNDATIONAL, true);
    }

    /**
     * @return array<string, bool>
     */
    public function statuses(): array
    {
        if (is_array($this->statuses)) {
            return $this->statuses;
        }

        return $this->statuses = Cache::rememberForever(
            $this->cacheKey(),
            fn (): array => $this->readStatuses(),
        );
    }

    /**
     * @return array<int, string>
     */
    public function enabled(): array
    {
        return array_keys(array_filter($this->statuses()));
    }

    /**
     * @return array<int, string>
     */
    public function disabled(): array
    {
        return array_keys(array_filter($this->statuses(), static fn (bool $enabled): bool => ! $enabled));
    }

    /**
     * @return array<int, string>
     */
    public function enabledNames(): array
    {
        return $this->enabled();
    }

    public function isEnabled(string $name): bool
    {
        return $this->statuses()[$name] ?? false;
    }

    public function requireEnabled(string $name): void
    {
        if (! $this->isEnabled($name)) {
            throw new NotFoundHttpException;
        }
    }

    public static function forgetCache(): void
    {
        Cache::forget(self::CACHE_KEY);

        $path = self::statusesPath();

        if (File::exists($path)) {
            Cache::forget(self::CACHE_KEY.':'.File::lastModified($path));
        }
    }

    /**
     * @return array<int, array{name: string, description: string, icon: string, navGroup: string, canBeDisabled: bool, enabled: bool}>
     */
    public function all(): array
    {
        $statuses = $this->statuses();

        return collect(Module::all())
            ->map(fn ($module) => [
                'name' => $module->getName(),
                'description' => $module->getDescription(),
                'icon' => $module->get('icon', 'bx-package'),
                'navGroup' => $module->get('navGroup', $module->getName()),
                'canBeDisabled' => ! $this->isFoundational($module->getName()),
                'enabled' => $statuses[$module->getName()] ?? false,
            ])
            ->sortBy('name')
            ->values()
            ->all();
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

    private function cacheKey(): string
    {
        $path = self::statusesPath();

        return File::exists($path)
            ? self::CACHE_KEY.':'.File::lastModified($path)
            : self::CACHE_KEY;
    }

    private static function statusesPath(): string
    {
        return config('modules.activators.file.statuses-file', base_path('modules_statuses.json'));
    }

    /**
     * @return array<string, bool>
     */
    private function readStatuses(): array
    {
        $path = self::statusesPath();

        if (! File::exists($path)) {
            return [];
        }

        $decoded = json_decode(File::get($path), true);

        if (! is_array($decoded)) {
            return [];
        }

        return array_map(static fn ($enabled): bool => (bool) $enabled, $decoded);
    }
}
