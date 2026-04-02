@php
    $timelineComponent = new class($groups, $emptyHeading, $emptyDescription, $hasMore) {
        public function __construct(
            protected array $groups,
            protected string $emptyHeading,
            protected string $emptyDescription,
            protected bool $hasMore,
        ) {}

        public function getTimelineCollapsedState(): array
        {
            return collect($this->groups)
                ->mapWithKeys(fn (array $group): array => [$group['date_key'] => (bool) ($group['collapsed'] ?? false)])
                ->all();
        }

        public function hasTimelineLoadMore(): bool
        {
            return $this->hasMore;
        }

        public function getTimelineLoadMoreAction(): string
        {
            return 'loadMore';
        }

        public function getTimelineLoadMoreLabel(): string
        {
            return 'Load more';
        }
    };
@endphp

@include('filament-timeline-view::partials.timeline')
