<?php

use Devletes\FilamentTimelineView\Infolists\Components\TimelineEntry;
use Devletes\FilamentTimelineView\Schemas\Components\Timeline;

it('prepares grouped timeline data from flat items on the schema component', function (): void {
    $component = Timeline::make('pulse')->items([
        [
            'id' => 1,
            'created_at' => '2026-04-10 09:00:00',
            'title' => 'First',
            'content' => 'First body',
        ],
        [
            'id' => 2,
            'created_at' => '2026-04-11 10:00:00',
            'title' => 'Second',
            'content' => 'Second body',
        ],
    ]);

    $groups = $component->getPreparedTimelineGroups();

    expect($groups)->toHaveCount(2)
        ->and($groups[0]['date_key'])->toBe('2026-04-10')
        ->and($groups[0]['items'][0]['title'])->toBe('First')
        ->and($groups[0]['display']['primary'])->toBe('Friday')
        ->and($groups[0]['display']['secondary'])->toBe('Apr 10')
        ->and($groups[1]['date_key'])->toBe('2026-04-11');
});

it('falls back to empty state defaults when no items are provided to the schema component', function (): void {
    $component = Timeline::make('pulse');

    expect($component->getPreparedTimelineGroups())->toBe([])
        ->and($component->getTimelineEmptyStateHeading())->toBe('Nothing to show yet')
        ->and($component->isTimelineCollapsible())->toBeFalse()
        ->and($component->hasTimelineLoadMore())->toBeFalse();
});

it('gates load-more on both the flag and an action on the schema component', function (): void {
    $component = Timeline::make('pulse')
        ->items([['created_at' => '2026-04-10 09:00:00', 'title' => 'x', 'content' => 'y']])
        ->hasMore(true);

    expect($component->hasTimelineLoadMore())->toBeFalse(); // no action set yet

    $component->loadMoreAction('loadMore');

    expect($component->hasTimelineLoadMore())->toBeTrue()
        ->and($component->getTimelineLoadMoreAction())->toBe('loadMore');
});

it('prepares grouped timeline data on the infolist TimelineEntry', function (): void {
    $component = TimelineEntry::make('history')->items([
        [
            'id' => 1,
            'created_at' => '2026-04-10 09:00:00',
            'title' => 'Entry',
            'content' => 'Entry body',
        ],
    ]);

    $groups = $component->getPreparedTimelineGroups();

    expect($groups)->toHaveCount(1)
        ->and($groups[0]['items'][0]['title'])->toBe('Entry')
        ->and($groups[0]['display']['primary'])->toBe('Friday');
});
