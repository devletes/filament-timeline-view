<?php

namespace Devletes\FilamentTimelineView\Concerns;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

trait NormalizesTimelineData
{
    /**
     * @param  array<int, mixed>|Collection<int, mixed>|Arrayable<int, mixed>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeTimelineItems(array|Collection|Arrayable $items): array
    {
        return collect($items instanceof Arrayable ? $items->toArray() : $items)
            ->map(fn (array $item): array => $this->normalizeTimelineItem($item))
            ->filter(fn (array $item): bool => $item['date_key'] !== '' && ($item['title'] !== '' || $item['content'] !== ''))
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>|Collection<int, mixed>|Arrayable<int, mixed>  $items
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeGroupedTimelineItems(array|Collection|Arrayable $items, string $dateKey): array
    {
        return collect($items instanceof Arrayable ? $items->toArray() : $items)
            ->map(fn (array $item): array => $this->normalizeTimelineItem($item, $dateKey))
            ->filter(fn (array $item): bool => $item['title'] !== '' || $item['content'] !== '')
            ->values()
            ->all();
    }

    /**
     * @param  array<int, mixed>|Collection<int, mixed>|Arrayable<int, mixed>  $groups
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeTimelineGroups(array|Collection|Arrayable $groups): array
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

    /**
     * @param  array<string, mixed>  $item
     * @return array<string, mixed>
     */
    protected function normalizeTimelineItem(array $item, ?string $fallbackDateKey = null): array
    {
        $createdAt = $this->normalizeTimelineDate($item['created_at'] ?? ($item['published_at'] ?? null));

        return [
            'id' => $item['id'] ?? null,
            'date_key' => (string) ($item['date_key'] ?? ($createdAt?->toDateString() ?? $fallbackDateKey ?? '')),
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
    }
}
