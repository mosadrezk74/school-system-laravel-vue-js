<?php

namespace Modules\Report\Services;

use Illuminate\Support\Collection;
use Modules\Core\Services\ModuleRegistry;
use Modules\Report\Contracts\ReportProviderInterface;
use Modules\Report\Exceptions\ReportOperationException;

class ReportRegistry
{
    /** @var array<string, ReportProviderInterface> */
    private array $providers = [];

    public function __construct(private readonly ModuleRegistry $modules) {}

    public function register(ReportProviderInterface $provider, ?string $module = null): void
    {
        if ($module !== null && ! $this->modules->isEnabled($module)) {
            return;
        }

        $this->providers[$provider->code()] = $provider;
    }

    /**
     * @return Collection<string, ReportProviderInterface>
     */
    public function all(): Collection
    {
        return collect($this->providers);
    }

    public function find(string $code): ?ReportProviderInterface
    {
        return $this->providers[$code] ?? null;
    }

    public function get(string $code): ReportProviderInterface
    {
        return $this->find($code) ?? throw ReportOperationException::providerNotFound();
    }

    public function has(string $code): bool
    {
        return isset($this->providers[$code]);
    }

    /**
     * @return array<int, string>
     */
    public function codes(): array
    {
        return array_keys($this->providers);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toMetadata(?string $search = null, ?string $category = null): array
    {
        return $this->all()
            ->map(fn (ReportProviderInterface $provider): array => [
                'code' => $provider->code(),
                'name' => $provider->name(),
                'category' => $provider->category(),
                'filters' => $provider->filters(),
                'columns' => $provider->columns(),
            ])
            ->when($category, fn (Collection $items) => $items->where('category', $category))
            ->when($search, fn (Collection $items) => $items->filter(
                fn (array $item): bool => mb_stripos($item['name'], $search) !== false
                    || mb_stripos($item['code'], $search) !== false,
            ))
            ->sortBy('name')
            ->values()
            ->all();
    }
}
