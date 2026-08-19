<?php

namespace Devletes\FilamentTimelineView\Tests\Fixtures;

use Devletes\FilamentTimelineView\Tables\Columns\TimelineEntry;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Workbench\App\Models\Pulse;

class StubTimelinePaginatedFiltersWidget extends TableWidget
{
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Pulse::query()->orderBy('id'))
            ->columns([
                TimelineEntry::make()->title('title'),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'news' => 'News',
                        'release' => 'Release',
                    ]),
            ])
            ->deferFilters(false)
            ->paginated([2])
            ->asTimeline();
    }
}
