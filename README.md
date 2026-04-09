# Filament Timeline View

Show chronological data as a timeline in Filament infolists, schemas/pages, and widgets.

## Requirements

- PHP `^8.2`
- Laravel `^11.0` or `^12.0`
- Filament `^5.0`

## Installation

```bash
composer require devletes/filament-timeline-view
```

The package registers its CSS asset with `FilamentAsset`, so it is picked up automatically when Filament publishes assets. If you run `php artisan filament:assets` in your app, the timeline styles will be bundled alongside Filament's own.

No config file is published — everything is configured fluently on the component or via widget method overrides.

## What it supports

- Filament **infolist** entries (`TimelineEntry`)
- Filament **schema** components for pages and custom layouts (`Timeline`)
- Filament **widgets** (`TimelineWidget`)
- Flat items, pre-grouped items, or mapped records
- Optional collapsible day groups
- Optional "load more" action, delegated to the host Livewire component

## Timeline item shape

```php
[
    'id' => 1,
    'created_at' => now(),
    'title' => 'Welcome to the Team',
    'content' => 'A quick introduction and welcome.',
    'url' => '/employee/pulse/1',
    'tags' => ['New Joiner'],
    'user' => [
        'name' => 'Sara Khan',
        'avatar_url' => 'https://example.com/avatar.jpg',
        'url' => '/employee/directory/1',
    ],
    'time_label' => '6:23 PM',
    'image_url' => 'https://example.com/post.jpg',
    'image_alt' => 'Welcome image',
]
```

`created_at` is the canonical timeline timestamp. The package derives the date grouping, the weekday label, and the time label from it by default. `date_key`, `date_label`, and `time_label` can still be passed as optional overrides when needed. Tags may be strings or `['label' => ..., 'url' => ...]` arrays.

## Data source precedence

When a component resolves its data, it uses the first of these that is non-empty:

1. `->groups([...])` — pre-grouped items, rendered as-is (date grouping is skipped).
2. `->items([...])` — flat items, grouped by `date_key` / `created_at` automatically.
3. `->records([...])` + `->mapItemUsing(fn ($record) => [...])` — an arbitrary collection that the mapper converts into the item shape above.

## Usage

### In a schema or page

```php
use Devletes\FilamentTimelineView\Schemas\Components\Timeline;

Timeline::make('pulse')
    ->items(fn () => $this->items)
    ->collapsible()
    ->hasMore(fn () => $this->hasMoreItems)
    ->loadMoreAction('loadMoreItems');
```

`loadMoreAction('loadMoreItems')` wires the load-more button to a Livewire method on the host component. The button only renders when both a `hasMore` condition and a `loadMoreAction` are set.

### In an infolist

```php
use Devletes\FilamentTimelineView\Infolists\Components\TimelineEntry;

TimelineEntry::make('history')
    ->records(fn ($record) => $record->activities)
    ->collapsible()
    ->mapItemUsing(fn ($activity) => [
        'created_at' => $activity->created_at,
        'title' => $activity->title,
        'content' => $activity->description,
    ]);
```

### As a widget

```php
use Devletes\FilamentTimelineView\Widgets\TimelineWidget;

class ActivityTimeline extends TimelineWidget
{
    protected bool $isCollapsible = true;

    protected function getTimelineItems(): array
    {
        return Activity::latest()->limit($this->visibleItemCount)->get()->all();
    }

    protected function getTimelineHasMore(): bool
    {
        return Activity::count() > $this->visibleItemCount;
    }
}
```

The widget owns its own `loadMore()` Livewire action, so no wiring is needed. Override `getLoadMoreIncrement()` to change the batch size (default `10`) and `getInitialVisibleItemCount()` for the starting window.

## Versioning

Packagist versions come from git tags. For pre-1.0 releases, use semver tags like `v0.1.0` or `v0.1.0-beta.1`.

## License

MIT. See [LICENSE.md](LICENSE.md).
