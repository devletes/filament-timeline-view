<?php

namespace Devletes\FilamentTimelineView\Widgets;

use Carbon\CarbonInterface;
use Filament\Widgets\Widget;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class TimelineWidget extends Widget
{
    protected static bool $isLazy = false;

    protected string $view = 'filament-timeline-view::widgets.timeline-widget';

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

    protected function getViewData(): array
    {
        return [
            'heading' => $this->getTimelineHeading(),
            'description' => $this->getTimelineDescription(),
            'emptyHeading' => $this->getEmptyStateHeading(),
            'emptyDescription' => $this->getEmptyStateDescription(),
            'groups' => $this->getPreparedGroups(),
            'hasMore' => $this->getTimelineHasMore(),
        ];
    }

    protected function getTimelineHeading(): string
    {
        return 'Timeline';
    }

    protected function getTimelineDescription(): ?string
    {
        return null;
    }

    protected function getEmptyStateHeading(): string
    {
        return 'Nothing to show yet';
    }

    protected function getEmptyStateDescription(): string
    {
        return 'Timeline items will appear here once data is provided.';
    }

    /**
     * @return array<int, array<string, mixed>>|Collection<int, array<string, mixed>>
     */
    protected function getTimelineItems(): array | Collection
    {
        return [];
    }

    /**
     * @return array<int, array<string, mixed>>|Collection<int, array<string, mixed>>
     */
    protected function getTimelineGroups(): array | Collection
    {
        return [];
    }

    protected function getTimelineHasMore(): bool
    {
        return false;
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

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function getPreparedGroups(): array
    {
        $groups = $this->resolveGroups();

        foreach ($groups as &$group) {
            $key = $group['date_key'];

            $this->collapsedDates[$key] ??= (bool) ($group['collapsed'] ?? false);

            $group['collapsed'] = (bool) $this->collapsedDates[$key];
            $group['collapsed_summary'] = $this->getCollapsedSummaryText(count($group['items']));
            $group['display'] = $this->resolveDateDisplay(
                $group['date_key'],
                $group['date_label'] ?: $group['date_key'],
            );
        }

        unset($group);

        return $groups;
    }

    protected function synchronizeCollapsedDates(): void
    {
        foreach ($this->resolveGroups() as $group) {
            $this->collapsedDates[$group['date_key']] ??= (bool) ($group['collapsed'] ?? false);
        }
    }

    protected function getCollapsedSummaryText(int $itemCount): string
    {
        return $itemCount === 1 ? '1 post hidden' : "{$itemCount} posts hidden";
    }

    /**
     * @return array{primary: string, secondary: ?string}
     */
    protected function resolveDateDisplay(string $dateKey, string $dateLabel): array
    {
        try {
            $date = Carbon::parse($dateKey);

            return [
                'primary' => $dateLabel,
                'secondary' => $date->format('M j'),
            ];
        } catch (\Throwable) {
            return [
                'primary' => $dateLabel,
                'secondary' => null,
            ];
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function resolveGroups(): array
    {
        $groups = $this->normalizeGroups($this->getTimelineGroups());

        if ($groups !== []) {
            return $groups;
        }

        return $this->groupItems($this->normalizeItems($this->getTimelineItems()));
    }

    /**
     * @param  array<int, array<string, mixed>>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function groupItems(array $items): array
    {
        return collect($items)
            ->groupBy(fn (array $item): string => $item['date_key'])
            ->map(function (Collection $groupItems, string $dateKey): array {
                $first = $groupItems->first();

                return [
                    'date_key' => $dateKey,
                    'date_label' => $first['date_label'] ?? $dateKey,
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
    protected function normalizeGroups(array | Collection | Arrayable $groups): array
    {
        return collect($groups instanceof Arrayable ? $groups->toArray() : $groups)
            ->map(function (array $group): array {
                $dateKey = (string) ($group['date_key'] ?? '');

                return [
                    'date_key' => $dateKey,
                    'date_label' => (string) ($group['date_label'] ?? $group['date_key'] ?? ''),
                    'collapsed' => (bool) ($group['collapsed'] ?? false),
                    'items' => $this->normalizeGroupedItems($group['items'] ?? [], $dateKey),
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
    protected function normalizeItems(array | Collection | Arrayable $items): array
    {
        return collect($items instanceof Arrayable ? $items->toArray() : $items)
            ->map(function (array $item): array {
                $publishedAt = $this->normalizeDate($item['published_at'] ?? null);

                return [
                    'id' => $item['id'] ?? null,
                    'date_key' => (string) ($item['date_key'] ?? ($publishedAt?->toDateString() ?? '')),
                    'date_label' => (string) ($item['date_label'] ?? ''),
                    'title' => (string) ($item['title'] ?? ''),
                    'content' => (string) ($item['content'] ?? ''),
                    'url' => $item['url'] ?? null,
                    'tags' => $this->normalizeTags($item['tags'] ?? []),
                    'user' => $this->normalizeUser($item['user'] ?? null),
                    'time_label' => (string) ($item['time_label'] ?? ($publishedAt?->format('g:i A') ?? '')),
                    'meta' => $item['meta'] ?? null,
                    'image_url' => $item['image_url'] ?? null,
                    'image_alt' => $item['image_alt'] ?? null,
                    'accent_color' => $item['accent_color'] ?? null,
                    'published_at' => $publishedAt,
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
    protected function normalizeGroupedItems(array | Collection | Arrayable $items, string $dateKey): array
    {
        return collect($items instanceof Arrayable ? $items->toArray() : $items)
            ->map(function (array $item) use ($dateKey): array {
                $publishedAt = $this->normalizeDate($item['published_at'] ?? null);

                return [
                    'id' => $item['id'] ?? null,
                    'date_key' => (string) ($item['date_key'] ?? $dateKey),
                    'date_label' => (string) ($item['date_label'] ?? ''),
                    'title' => (string) ($item['title'] ?? ''),
                    'content' => (string) ($item['content'] ?? ''),
                    'url' => $item['url'] ?? null,
                    'tags' => $this->normalizeTags($item['tags'] ?? []),
                    'user' => $this->normalizeUser($item['user'] ?? null),
                    'time_label' => (string) ($item['time_label'] ?? ($publishedAt?->format('g:i A') ?? '')),
                    'meta' => $item['meta'] ?? null,
                    'image_url' => $item['image_url'] ?? null,
                    'image_alt' => $item['image_alt'] ?? null,
                    'accent_color' => $item['accent_color'] ?? null,
                    'published_at' => $publishedAt,
                ];
            })
            ->filter(fn (array $item): bool => $item['title'] !== '' || $item['content'] !== '')
            ->values()
            ->all();
    }

    protected function normalizeDate(mixed $value): ?CarbonInterface
    {
        if ($value instanceof CarbonInterface) {
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
    protected function normalizeTags(mixed $tags): array
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
    protected function normalizeUser(mixed $user): ?array
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
}
