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
    'date_key' => '2026-04-02',
    'date_label' => 'Today',
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

## Basic Usage

### In a schema or page

```php
use Devletes\FilamentTimelineView\Schemas\Components\Timeline;

Timeline::make('pulse')
    ->items(fn () => $this->items)
    ->hasMore(fn () => $this->hasMoreItems)
    ->loadMoreAction('loadMoreItems');
```

### In an infolist

```php
use Devletes\FilamentTimelineView\Infolists\Components\TimelineEntry;

TimelineEntry::make('history')
    ->records(fn ($record) => $record->activities)
    ->mapItemUsing(fn ($activity) => [
        'published_at' => $activity->created_at,
        'title' => $activity->title,
        'content' => $activity->description,
    ]);
```

## Versioning

Packagist versions should come from git tags. For prereleases, use semantic version tags such as `v0.1.0-beta.1`.

