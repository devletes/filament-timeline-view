<?php

namespace Devletes\FilamentTimelineView\Tests\Fixtures;

use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class StubTimelineWidget extends TableWidget
{
    public string $variant = 'single';

    public function table(Table $table): Table
    {
        return match ($this->variant) {
            'double' => $table->columns([])->asDoubleSidedTimeline(),
            default => $table->columns([])->asTimeline(),
        };
    }
}
