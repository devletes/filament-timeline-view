<?php

use Devletes\FilamentTimelineView\Tests\Fixtures\StubTimelineWidget;
use Filament\Tables\Table;

it('registers asTimeline and asDoubleSidedTimeline macros on the Table class', function () {
    expect(Table::hasMacro('asTimeline'))->toBeTrue();
    expect(Table::hasMacro('asDoubleSidedTimeline'))->toBeTrue();
});

it('asTimeline switches the table view to the timeline layout with single variant', function () {
    $widget = new StubTimelineWidget;
    $widget->variant = 'single';

    $table = $widget->table(Table::make($widget));

    expect($table->getView())->toBe('filament-timeline-view::tables.timeline');
    expect($table->getViewData())->toMatchArray(['timelineLayout' => 'single']);
});

it('asDoubleSidedTimeline switches the table view to the timeline layout with double variant', function () {
    $widget = new StubTimelineWidget;
    $widget->variant = 'double';

    $table = $widget->table(Table::make($widget));

    expect($table->getView())->toBe('filament-timeline-view::tables.timeline');
    expect($table->getViewData())->toMatchArray(['timelineLayout' => 'double']);
});
