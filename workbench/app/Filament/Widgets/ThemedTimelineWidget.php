<?php

namespace Workbench\App\Filament\Widgets;

use Devletes\FilamentTimelineView\Tables\Columns\TimelineEntry;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Workbench\App\Models\Pulse;

class ThemedTimelineWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected string $view = 'workbench::themed-table-widget';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Themed timeline')
            ->description('Per-instance overrides for --ftv-line-color, --ftv-card-surface, and --ftv-card-ring.')
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
            ->paginated([3])
            ->asTimeline();
    }
}
