<?php

namespace Workbench\App\Filament\Widgets;

use Devletes\FilamentTimelineView\Tables\Columns\TimelineEntry;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Workbench\App\Models\Pulse;

class FiltersLayoutWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $layout = $this->resolveLayout();

        return $table
            ->heading('Company Pulse')
            ->description(filled(request()->query('layout'))
                ? str($layout->name)->headline()->toString().' filters layout'
                : 'Latest updates across the company.')
            ->query(fn () => Pulse::query()->with('author'))
            ->defaultSort('published_at', 'desc')
            ->columns([
                TimelineEntry::make()
                    ->title('title')
                    ->content('body')
                    ->author('author.name', fn () => '/avatar.png')
                    ->time('published_at'),
            ])
            ->defaultGroup(
                Group::make('published_at')
                    ->date()
                    ->orderQueryUsing(fn ($query) => $query->orderByDesc('published_at')),
            )
            ->filters([
                SelectFilter::make('category')
                    ->multiple()
                    ->options(fn (): array => Pulse::query()
                        ->whereNotNull('category')
                        ->distinct()
                        ->orderBy('category')
                        ->pluck('category', 'category')
                        ->all())
                    // README screenshot helper: ?category=Announcement,Article renders the indicator row.
                    ->default(array_filter(explode(',', (string) request()->query('category')))),
            ], $layout)
            ->asTimeline();
    }

    protected function resolveLayout(): FiltersLayout
    {
        return match (request()->query('layout')) {
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
        };
    }
}
