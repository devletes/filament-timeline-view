<?php

namespace Devletes\FilamentTimelineView\Widgets;

use Devletes\FilamentTimelineView\Concerns\NormalizesTimelineData;
use Filament\Widgets\Widget;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class TimelineWidget extends Widget
{
    use NormalizesTimelineData;

    protected static bool $isLazy = false;

    protected string $view = 'filament-timeline-view::widgets.timeline-widget';

    protected bool $isCollapsible = false;

    public int $visibleItemCount = 10;

    /**
     * @var array<string, bool>
     */
    public array $collapsedDates = [];

    public function mount(): void
    {
        $this->visibleItemCount = $this->getInitialVisibleItemCount();
        $this->synchronizeCollapsedDates();
    }

    public function toggleDate(string $dateKey): void
    {
        $this->collapsedDates[$dateKey] = ! ($this->collapsedDates[$dateKey] ?? false);
    }

    public function loadMore(): void
    {
        $this->visibleItemCount += $this->getLoadMoreIncrement();

        if (($handler = $this->getLoadMoreHandler()) && method_exists($this, $handler)) {
            $this->{$handler}();
        }

        $this->dispatch('timeline-view-load-more', visibleItemCount: $this->visibleItemCount);

        $this->synchronizeCollapsedDates();
    }

    public function getTimelineEmptyStateHeading(): string
    {
        return 'Nothing to show yet';
    }

    public function getTimelineEmptyStateDescription(): string
    {
        return 'Timeline items will appear here once data is provided.';
    }

    /**
     * @return array<int, array<string, mixed>>|Collection<int, array<string, mixed>>
     */
    protected function getTimelineItems(): array|Collection
    {
        return [];
    }

    /**
     * @return array<int, array<string, mixed>>|Collection<int, array<string, mixed>>
     */
    protected function getTimelineGroups(): array|Collection
    {
        return [];
    }

    protected function getTimelineHasMore(): bool
    {
        return false;
    }

    public function isTimelineCollapsible(): bool
    {
        return $this->isCollapsible;
    }

    public function hasTimelineLoadMore(): bool
    {
        return $this->getTimelineHasMore();
    }

    public function getTimelineLoadMoreAction(): string
    {
        return 'loadMore';
    }

    public function getTimelineLoadMoreLabel(): string
    {
        return 'Load more';
    }

    /**
     * @return array<string, bool>
     */
    public function getTimelineCollapsedState(): array
    {
        return collect($this->getPreparedTimelineGroups())
            ->mapWithKeys(fn (array $group): array => [$group['date_key'] => (bool) ($group['collapsed'] ?? false)])
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPreparedTimelineGroups(): array
    {
        $groups = $this->resolveGroups();

        foreach ($groups as &$group) {
            $key = $group['date_key'];

            $this->collapsedDates[$key] ??= (bool) ($group['collapsed'] ?? false);

            $group['collapsed'] = (bool) $this->collapsedDates[$key];
            $group['collapsed_summary'] = $this->getCollapsedSummaryText(count($group['items']));
            $group['display'] = $this->resolveDateDisplay(
                $group['date_key'],
                $group['date_label'] ?? null,
            );
        }

        unset($group);

        return $groups;
    }

    protected function getLoadMoreIncrement(): int
    {
        return 10;
    }

    protected function getInitialVisibleItemCount(): int
    {
        return 10;
    }

    protected function getLoadMoreHandler(): ?string
    {
        return null;
    }

    protected function synchronizeCollapsedDates(): void
    {
        foreach ($this->resolveGroups() as $group) {
            $this->collapsedDates[$group['date_key']] ??= (bool) ($group['collapsed'] ?? false);
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function resolveGroups(): array
    {
        $groups = $this->normalizeTimelineGroups($this->toTimelineArrayable($this->getTimelineGroups()));

        if ($groups !== []) {
            return $groups;
        }

        return $this->groupTimelineItems(
            $this->normalizeTimelineItems($this->toTimelineArrayable($this->getTimelineItems())),
        );
    }

    /**
     * @return array<int, mixed>|Collection<int, mixed>|Arrayable<int, mixed>
     */
    protected function toTimelineArrayable(mixed $value): array|Collection|Arrayable
    {
        if (is_array($value) || $value instanceof Collection || $value instanceof Arrayable) {
            return $value;
        }

        return [];
    }
}
