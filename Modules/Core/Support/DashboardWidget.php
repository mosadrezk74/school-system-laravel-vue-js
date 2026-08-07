<?php

namespace Modules\Core\Support;

use Closure;

/**
 * One dashboard tile declared as data rather than markup.
 *
 * A widget states what it needs to be visible — an enabled module, a
 * permission, an arbitrary rule — and the collector decides. Nothing is
 * hardcoded in the page, so disabling a module removes its tiles everywhere.
 */
final class DashboardWidget
{
    private ?string $module = null;

    private ?string $permission = null;

    private ?Closure $visibleWhen = null;

    private int $priority = 100;

    private string $icon = 'bx-bar-chart-alt-2';

    private string $color = 'primary';

    private ?string $route = null;

    private mixed $value = null;

    private function __construct(
        public readonly string $id,
        private Closure $resolver,
    ) {}

    public static function make(string $id, Closure $resolver): self
    {
        return new self($id, $resolver);
    }

    public function module(?string $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function permission(?string $permission): self
    {
        $this->permission = $permission;

        return $this;
    }

    public function visibleWhen(Closure $callback): self
    {
        $this->visibleWhen = $callback;

        return $this;
    }

    public function priority(int $priority): self
    {
        $this->priority = $priority;

        return $this;
    }

    public function icon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function color(string $color): self
    {
        $this->color = $color;

        return $this;
    }

    public function route(?string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function requiredModule(): ?string
    {
        return $this->module;
    }

    public function requiredPermission(): ?string
    {
        return $this->permission;
    }

    public function rule(): ?Closure
    {
        return $this->visibleWhen;
    }

    public function order(): int
    {
        return $this->priority;
    }

    public function resolve(): self
    {
        $this->value = ($this->resolver)();

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'value' => $this->value,
            'icon' => $this->icon,
            'color' => $this->color,
            'route' => $this->route,
        ];
    }
}
