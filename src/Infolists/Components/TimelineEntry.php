<?php

namespace Devletes\FilamentTimelineView\Infolists\Components;

use Devletes\FilamentTimelineView\Concerns\HasTimeline;
use Filament\Infolists\Components\Entry;

class TimelineEntry extends Entry
{
    use HasTimeline;

    protected string $view = 'filament-timeline-view::infolists.components.timeline-entry';

    protected function setUp(): void
    {
        parent::setUp();

        $this->hiddenLabel();
        $this->entryWrapperView('filament-timeline-view::timeline-entry-wrapper');
    }
}
