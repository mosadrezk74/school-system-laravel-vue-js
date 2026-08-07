<?php

namespace Modules\Core\Services;

use Modules\Core\Support\DashboardWidget;

class DashboardWidgetCollector
{
    public function __construct(private readonly ModuleRegistry $modules) {}

    /**
     * Resolves only the widgets the signed-in user may actually see, so a
     * disabled module or a missing permission removes the tile without the
     * page knowing anything about it.
     *
     * @param  array<int, DashboardWidget>  $widgets
     * @return array<int, array<string, mixed>>
     */
    public function collect(array $widgets): array
    {
        return collect($widgets)
            ->filter(fn (DashboardWidget $widget): bool => $this->isVisible($widget))
            ->sortBy(fn (DashboardWidget $widget): int => $widget->order())
            ->map(fn (DashboardWidget $widget): array => $widget->resolve()->toArray())
            ->values()
            ->all();
    }

    private function isVisible(DashboardWidget $widget): bool
    {
        return $this->moduleAllows($widget)
            && $this->permissionAllows($widget)
            && $this->ruleAllows($widget);
    }

    private function moduleAllows(DashboardWidget $widget): bool
    {
        $module = $widget->requiredModule();

        return $module === null || $this->modules->isEnabled($module);
    }

    private function permissionAllows(DashboardWidget $widget): bool
    {
        $permission = $widget->requiredPermission();

        return $permission === null || (bool) auth()->user()?->can($permission);
    }

    private function ruleAllows(DashboardWidget $widget): bool
    {
        $rule = $widget->rule();

        return $rule === null || (bool) $rule();
    }
}
