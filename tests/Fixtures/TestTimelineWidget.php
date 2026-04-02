<?php

namespace Devletes\FilamentTimelineView\Tests\Fixtures;

use Devletes\FilamentTimelineView\Widgets\TimelineWidget;
use Illuminate\Support\Collection;

class TestTimelineWidget extends TimelineWidget
{
    public static array $items = [];

    public static array $groups = [];

    public static bool $hasMore = false;

    public static bool $timelineIsCollapsible = false;

    public static int $loadMoreCalls = 0;

    protected function getTimelineHeading(): string
    {
        return 'Test Timeline';
    }

    protected function getTimelineDescription(): ?string
    {
        return 'Generic timeline';
    }

    protected function getTimelineItems(): array | Collection
    {
        return static::$items;
    }

    protected function getTimelineGroups(): array | Collection
    {
        return static::$groups;
    }

    protected function getTimelineHasMore(): bool
    {
        return static::$hasMore;
    }

    public function isTimelineCollapsible(): bool
    {
        return static::$timelineIsCollapsible;
    }

    protected function getLoadMoreHandler(): ?string
    {
        return 'handleTimelineLoadMore';
    }

    public function handleTimelineLoadMore(): void
    {
        static::$loadMoreCalls++;
    }
}
