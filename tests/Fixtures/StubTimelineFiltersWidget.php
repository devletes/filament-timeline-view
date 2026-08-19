<?php

namespace Devletes\FilamentTimelineView\Tests\Fixtures;

use Devletes\FilamentTimelineView\Tables\Columns\TimelineEntry;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Workbench\App\Models\Pulse;

class StubTimelineFiltersWidget extends TableWidget
{
    protected static bool $isLazy = false;

    public string $layout = 'dropdown';

    public bool $searchable = false;

    public function mount(string $layout = 'dropdown', bool $searchable = false): void
    {
        $this->layout = $layout;
        $this->searchable = $searchable;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => Pulse::query())
            ->columns([
                TimelineEntry::make()->title('title'),
            ])
            ->searchable($this->searchable ? ['title'] : false)
            ->filters([
                SelectFilter::make('category')
                    ->options([
                        'news' => 'News',
                        'release' => 'Release',
                    ]),
            ], match ($this->layout) {
                'modal' => FiltersLayout::Modal,
                'above' => FiltersLayout::AboveContent,
                'above-collapsible' => FiltersLayout::AboveContentCollapsible,
                'below' => FiltersLayout::BelowContent,
                'before' => FiltersLayout::BeforeContent,
                'before-collapsible' => FiltersLayout::BeforeContentCollapsible,
                'after' => FiltersLayout::AfterContent,
                'after-collapsible' => FiltersLayout::AfterContentCollapsible,
                'hidden' => FiltersLayout::Hidden,
                default => FiltersLayout::Dropdown,
            })
            ->asTimeline();
    }
}
