<?php

namespace Devletes\FilamentTimelineView\Concerns;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

trait HasTimeline
{
    protected array | Collection | Arrayable | Closure $timelineItems = [];

    protected array | Collection | Arrayable | Closure $timelineGroups = [];

    protected array | Collection | Arrayable | Closure $timelineRecords = [];

    protected ?Closure $timelineItemMapper = null;

    protected bool | Closure $timelineHasMore = false;

    protected bool | Closure $isTimelineCollapsible = false;

    protected bool | Closure $isTimelineLoadMoreEnabled = false;

    protected string | Closure | null $timelineLoadMoreAction = null;

    protected string | Closure $timelineLoadMoreLabel = 'Load more';

    protected string | Closure $timelineEmptyStateHeading = 'Nothing to show yet';

    protected string | Closure $timelineEmptyStateDescription = 'Timeline items will appear here once data is provided.';

    public function items(array | Collection | Arrayable | Closure $items): static
    {
        $this->timelineItems = $items;

        return $this;
    }

    public function groups(array | Collection | Arrayable | Closure $groups): static
    {
        $this->timelineGroups = $groups;

        return $this;
    }

    public function records(array | Collection | Arrayable | Closure $records): static
    {
        $this->timelineRecords = $records;

        return $this;
    }

    public function mapItemUsing(?Closure $callback): static
    {
        $this->timelineItemMapper = $callback;

        return $this;
    }

    public function hasMore(bool | Closure $condition = true): static
    {
        $this->timelineHasMore = $condition;

        return $this;
    }

    public function collapsible(bool | Closure $condition = true): static
    {
        $this->isTimelineCollapsible = $condition;

        return $this;
    }

    public function loadMore(bool | Closure $condition = true): static
    {
        $this->isTimelineLoadMoreEnabled = $condition;

        return $this;
    }

    public function loadMoreAction(string | Closure | null $action): static
    {
        $this->timelineLoadMoreAction = $action;
        $this->loadMore(filled($action));

        return $this;
    }

    public function loadMoreLabel(string | Closure $label): static
    {
        $this->timelineLoadMoreLabel = $label;

        return $this;
    }

    public function emptyStateHeading(string | Closure $heading): static
    {
        $this->timelineEmptyStateHeading = $heading;

        return $this;
    }

    public function emptyStateDescription(string | Closure $description): static
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

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function groupTimelineItems(array $items): array
    {
        return collect($items)
            ->groupBy(fn (array $item): string => $item['date_key'])
            ->map(function (Collection $groupItems, string $dateKey): array {
                $first = $groupItems->first();

                return [
                    'date_key' => $dateKey,
                    'date_label' => $first['date_label'] ?? null,
                    'created_at' => $first['created_at'] ?? null,
                    'collapsed' => false,
                    'items' => $groupItems->values()->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>|Collection<int, array<string, mixed>>|Arrayable<int, array<string, mixed>>  $groups
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeTimelineGroups(array | Collection | Arrayable $groups): array
    {
        return collect($groups instanceof Arrayable ? $groups->toArray() : $groups)
            ->map(function (array $group): array {
                $items = $this->normalizeGroupedTimelineItems($group['items'] ?? [], (string) ($group['date_key'] ?? ''));
                $createdAt = $this->normalizeTimelineDate($group['created_at'] ?? null)
                    ?? collect($items)->pluck('created_at')->filter()->first();
                $dateKey = (string) ($group['date_key'] ?? ($createdAt?->toDateString() ?? ''));

                return [
                    'date_key' => $dateKey,
                    'date_label' => filled($group['date_label'] ?? null) ? (string) $group['date_label'] : null,
                    'created_at' => $createdAt,
                    'collapsed' => (bool) ($group['collapsed'] ?? false),
                    'items' => $items,
                ];
            })
            ->filter(fn (array $group): bool => $group['date_key'] !== '')
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>|Collection<int, array<string, mixed>>|Arrayable<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeTimelineItems(array | Collection | Arrayable $items): array
    {
        return collect($items instanceof Arrayable ? $items->toArray() : $items)
            ->map(function (array $item): array {
                $createdAt = $this->normalizeTimelineDate($item['created_at'] ?? ($item['published_at'] ?? null));

                return [
                    'id' => $item['id'] ?? null,
                    'date_key' => (string) ($item['date_key'] ?? ($createdAt?->toDateString() ?? '')),
                    'date_label' => filled($item['date_label'] ?? null) ? (string) $item['date_label'] : null,
                    'title' => (string) ($item['title'] ?? ''),
                    'content' => (string) ($item['content'] ?? ''),
                    'url' => $item['url'] ?? null,
                    'tags' => $this->normalizeTimelineTags($item['tags'] ?? []),
                    'user' => $this->normalizeTimelineUser($item['user'] ?? null),
                    'time_label' => (string) ($item['time_label'] ?? ($createdAt?->format('g:i A') ?? '')),
                    'meta' => $item['meta'] ?? null,
                    'image_url' => $item['image_url'] ?? null,
                    'image_alt' => $item['image_alt'] ?? null,
                    'accent_color' => $item['accent_color'] ?? null,
                    'created_at' => $createdAt,
                ];
            })
            ->filter(fn (array $item): bool => $item['date_key'] !== '' && ($item['title'] !== '' || $item['content'] !== ''))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>|Collection<int, array<string, mixed>>|Arrayable<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeGroupedTimelineItems(array | Collection | Arrayable $items, string $dateKey): array
    {
        return collect($items instanceof Arrayable ? $items->toArray() : $items)
            ->map(function (array $item) use ($dateKey): array {
                $createdAt = $this->normalizeTimelineDate($item['created_at'] ?? ($item['published_at'] ?? null));

                return [
                    'id' => $item['id'] ?? null,
                    'date_key' => (string) ($item['date_key'] ?? ($createdAt?->toDateString() ?? $dateKey)),
                    'date_label' => filled($item['date_label'] ?? null) ? (string) $item['date_label'] : null,
                    'title' => (string) ($item['title'] ?? ''),
                    'content' => (string) ($item['content'] ?? ''),
                    'url' => $item['url'] ?? null,
                    'tags' => $this->normalizeTimelineTags($item['tags'] ?? []),
                    'user' => $this->normalizeTimelineUser($item['user'] ?? null),
                    'time_label' => (string) ($item['time_label'] ?? ($createdAt?->format('g:i A') ?? '')),
                    'meta' => $item['meta'] ?? null,
                    'image_url' => $item['image_url'] ?? null,
                    'image_alt' => $item['image_alt'] ?? null,
                    'accent_color' => $item['accent_color'] ?? null,
                    'created_at' => $createdAt,
                ];
            })
            ->filter(fn (array $item): bool => $item['title'] !== '' || $item['content'] !== '')
            ->values()
            ->all();
    }

    protected function getCollapsedSummaryText(int $itemCount): string
    {
        return $itemCount === 1 ? '1 post hidden' : "{$itemCount} posts hidden";
    }

    /**
     * @return array{primary: string, secondary: ?string}
     */
    protected function resolveDateDisplay(string $dateKey, ?string $dateLabel = null): array
    {
        try {
            $date = Carbon::parse($dateKey);

            return [
                'primary' => filled($dateLabel) ? $dateLabel : ($date->isToday() ? 'Today' : $date->format('l')),
                'secondary' => $date->format('M j'),
            ];
        } catch (\Throwable) {
            return [
                'primary' => $dateLabel ?: $dateKey,
                'secondary' => null,
            ];
        }
    }

    protected function normalizeTimelineDate(mixed $value): ?Carbon
    {
        if ($value instanceof Carbon) {
            return $value;
        }

        if (blank($value)) {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<int, array{label: string, url: ?string}>
     */
    protected function normalizeTimelineTags(mixed $tags): array
    {
        return collect($tags instanceof Arrayable ? $tags->toArray() : $tags)
            ->map(function (mixed $tag): ?array {
                if (blank($tag)) {
                    return null;
                }

                if (is_string($tag)) {
                    return [
                        'label' => $tag,
                        'url' => null,
                    ];
                }

                if (! is_array($tag)) {
                    return null;
                }

                $label = (string) ($tag['label'] ?? '');

                if ($label === '') {
                    return null;
                }

                return [
                    'label' => $label,
                    'url' => filled($tag['url'] ?? null) ? (string) $tag['url'] : null,
                ];
            })
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array{name: string, avatar_url: ?string, url: ?string}|null
     */
    protected function normalizeTimelineUser(mixed $user): ?array
    {
        if (! is_array($user)) {
            return null;
        }

        $name = (string) ($user['name'] ?? '');

        if ($name === '') {
            return null;
        }

        return [
            'name' => $name,
            'avatar_url' => filled($user['avatar_url'] ?? null) ? (string) $user['avatar_url'] : null,
            'url' => filled($user['url'] ?? null) ? (string) $user['url'] : null,
        ];
    }

    /**
     * @return Collection<int, mixed>
     */
    protected function toTimelineCollection(mixed $items): Collection
    {
        if ($items instanceof Collection) {
            return $items;
        }

        if ($items instanceof Arrayable) {
            return collect($items->toArray());
        }

        return collect($items);
    }

    /**
     * @return array<int, mixed>
     */
    protected function toTimelineArray(mixed $items): array
    {
        return $this->toTimelineCollection($items)
            ->values()
            ->all();
    }
}
