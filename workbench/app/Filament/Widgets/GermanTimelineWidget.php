<?php

namespace Workbench\App\Filament\Widgets;

use Devletes\FilamentTimelineView\Tables\Columns\TimelineEntry;
use Filament\Actions\Action;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Workbench\App\Models\Pulse;

class GermanTimelineWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        app()->setLocale('de');

        return $table
            ->heading('Zeitleiste auf Deutsch')
            ->description('Dieselbe Tabelle, App-Sprache auf "de" gesetzt.')
            ->query(fn () => Pulse::query()->with('author'))
            ->defaultSort('published_at', 'desc')
            ->columns([
                TimelineEntry::make()
                    ->title('title')
                    ->content('body')
                    ->author('author.name', fn () => '/avatar.png')
                    ->time('published_at', 'H:i'),
            ])
            ->defaultGroup(
                Group::make('published_at')
                    ->date()
                    ->collapsible()
                    ->orderQueryUsing(fn ($query) => $query->orderByDesc('published_at')),
            )
            ->recordActions([
                Action::make('view')->label('Anzeigen')->icon('heroicon-m-eye'),
                Action::make('delete')->label('Löschen')->icon('heroicon-m-trash')->color('danger'),
            ])
            ->paginated([3])
            ->asTimeline();
    }
}
