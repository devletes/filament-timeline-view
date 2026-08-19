<?php

use Devletes\FilamentTimelineView\Tables\Columns\TimelineEntry;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Livewire\Livewire;
use Workbench\App\Models\Pulse;

class StubSearchableTimelineWidget extends TableWidget
{
    protected static bool $isLazy = false;

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Pulse::query())
            ->columns([
                TimelineEntry::make()->title('title'),
            ])
            ->searchable()
            ->asTimeline();
    }
}

beforeEach(function () {
    Pulse::create(['title' => 'Findable', 'body' => 'Body.', 'published_at' => now()]);
});

it('renders without error when the table is searchable', function () {
    Livewire::test(StubSearchableTimelineWidget::class)
        ->assertOk()
        ->assertSee('Findable');
});

// The timeline replaces the whole table view and does not render a search field
// yet. Asserted so the day one is added, this test is the reminder to update it.
it('does not yet render a search field', function () {
    Livewire::test(StubSearchableTimelineWidget::class)
        ->assertDontSeeHtml('fi-ta-search-field');
});
