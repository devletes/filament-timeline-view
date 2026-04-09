<?php

namespace Devletes\FilamentTimelineView\Concerns;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

trait HasTimeline
{
    use NormalizesTimelineData;

    protected array|Collection|Arrayable|Closure $timelineItems = [];

    protected array|Collection|Arrayable|Closure $timelineGroups = [];

    protected array|Collection|Arrayable|Closure $timelineRecords = [];

    protected ?Closure $timelineItemMapper = null;

    protected bool|Closure $timelineHasMore = false;

    protected bool|Closure $isTimelineCollapsible = false;

    protected bool|Closure $isTimelineLoadMoreEnabled = false;

    protected string|Closure|null $timelineLoadMoreAction = null;

    protected string|Closure $timelineLoadMoreLabel = 'Load more';

    protected string|Closure $timelineEmptyStateHeading = 'Nothing to show yet';

    protected string|Closure $timelineEmptyStateDescription = 'Timeline items will appear here once data is provided.';

    public function items(array|Collection|Arrayable|Closure $items): static
    {
        $this->timelineItems = $items;

        return $this;
    }

    public function groups(array|Collection|Arrayable|Closure $groups): static
    {
        $this->timelineGroups = $groups;

        return $this;
    }

    public function records(array|Collection|Arrayable|Closure $records): static
    {
        $this->timelineRecords = $records;

        return $this;
    }

    public function mapItemUsing(?Closure $callback): static
    {
        $this->timelineItemMapper = $callback;

        return $this;
    }

    public function hasMore(bool|Closure $condition = true): static
    {
        $this->timelineHasMore = $condition;

        return $this;
    }

    public function collapsible(bool|Closure $condition = true): static
    {
        $this->isTimelineCollapsible = $condition;

        return $this;
    }

    public function loadMore(bool|Closure $condition = true): static
    {
        $this->isTimelineLoadMoreEnabled = $condition;

        return $this;
    }

    public function loadMoreAction(string|Closure|null $action): static
    {
        $this->timelineLoadMoreAction = $action;
        $this->loadMore(filled($action));

        return $this;
    }

    public function loadMoreLabel(string|Closure $label): static
    {
        $this->timelineLoadMoreLabel = $label;

        return $this;
    }

    public function emptyStateHeading(string|Closure $heading): static
    {
        $this->timelineEmptyStateHeading = $heading;

        return $this;
    }

    public function emptyStateDescription(string|Closure $description): static
    {
        $this->timelineEmptyStateDescription = $description;

        return $this;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPreparedTimelineGroups(): array
    {
        $groups = $this->resolveTimelineGroups();

        foreach ($groups as &$group) {
            $group['collapsed'] = (bool) ($group['collapsed'] ?? false);
            $group['collapsed_summary'] = $this->getCollapsedSummaryText(count($group['items']));
            $group['display'] = $this->resolveDateDisplay(
                $group['date_key'],
                $group['date_label'] ?? null,
            );
        }

        unset($group);

        return $groups;
    }

    public function getTimelineEmptyStateHeading(): string
    {
        return (string) $this->evaluate($this->timelineEmptyStateHeading);
    }

    public function getTimelineEmptyStateDescription(): string
    {
        return (string) $this->evaluate($this->timelineEmptyStateDescription);
    }

    public function hasTimelineLoadMore(): bool
    {
        if (! $this->evaluate($this->isTimelineLoadMoreEnabled)) {
            return false;
        }

        if (! filled($this->getTimelineLoadMoreAction())) {
            return false;
        }

        return (bool) $this->evaluate($this->timelineHasMore);
    }

    public function isTimelineCollapsible(): bool
    {
        return (bool) $this->evaluate($this->isTimelineCollapsible);
    }

    public function getTimelineLoadMoreAction(): ?string
    {
        $action = $this->evaluate($this->timelineLoadMoreAction);

        return filled($action) ? (string) $action : null;
    }

    public function getTimelineLoadMoreLabel(): string
    {
        return (string) $this->evaluate($this->timelineLoadMoreLabel);
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
    protected function resolveTimelineGroups(): array
    {
        $groups = $this->normalizeTimelineGroups($this->evaluate($this->timelineGroups));

        if ($groups !== []) {
            return $groups;
        }

        $items = $this->normalizeTimelineItems($this->resolveTimelineItems());

        return $this->groupTimelineItems($items);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function resolveTimelineItems(): array
    {
        $items = $this->evaluate($this->timelineItems);

        if (filled($items) && collect($items)->isNotEmpty()) {
            return $this->toTimelineArray($items);
        }

        $records = $this->toTimelineCollection($this->evaluate($this->timelineRecords));

        if ($records->isEmpty()) {
            return [];
        }

        if (! $this->timelineItemMapper instanceof Closure) {
            return $this->toTimelineArray($records);
        }

        return $records
            ->map(fn (mixed $record): mixed => $this->evaluate($this->timelineItemMapper, [
                'item' => $record,
                'recordItem' => $record,
                'timelineRecord' => $record,
            ]))
            ->filter()
            ->values()
            ->all();
    }
}
