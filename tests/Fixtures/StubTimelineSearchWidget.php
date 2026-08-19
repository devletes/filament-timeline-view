<?php

namespace Devletes\FilamentTimelineView\Tests\Fixtures;

use Devletes\FilamentTimelineView\Tables\Columns\TimelineEntry;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Workbench\App\Models\Pulse;

class StubTimelineSearchWidget extends TableWidget
{
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Pulse::query())
            ->columns([
                TimelineEntry::make()->title('title'),
            ])
            ->searchable(['title'])
            ->asTimeline();
    }
}
