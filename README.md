# Filament Timeline View

Show chronological data as a timeline in Filament infolists, pages, and widgets.

## Installation

```bash
composer require devletes/filament-timeline-view
```

## What It Supports

- Filament infolist entries
- Filament schema components for pages and custom layouts
- Filament widgets
- Flat items, grouped items, or mapped records
- Optional load-more actions controlled by the host Livewire component

## Timeline Item Shape

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

`created_at` is the canonical timeline timestamp. The package derives the date grouping, weekday label, and time label from it by default. `date_key`, `date_label`, and `time_label` can still be passed as optional overrides when needed.

## Basic Usage

### In a schema or page

```php
use Devletes\FilamentTimelineView\Schemas\Components\Timeline;

Timeline::make('pulse')
    ->items(fn () => $this->items)
    ->collapsible()
    ->hasMore(fn () => $this->hasMoreItems)
    ->loadMoreAction('loadMoreItems');
```

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

## Versioning

Packagist versions should come from git tags. For prereleases, use semantic version tags such as `v0.1.0-beta.1`.
