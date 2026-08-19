<?php

namespace Devletes\FilamentTimelineView\Tests\Fixtures;

use Devletes\FilamentTimelineView\Tables\Columns\TimelineEntry;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Workbench\App\Models\Pulse;

class StubTimelineActionsWidget extends TableWidget
{
    protected static bool $isLazy = false;

    /**
     * One of: `duplicate-url`, `duplicate-action`, `custom-url`, `custom-action`.
     */
    public string $mode = 'duplicate-url';

    public function mount(string $mode = 'duplicate-url'): void
    {
        $this->mode = $mode;
    }

    public function table(Table $table): Table
    {
        $table = $table
            ->query(fn () => Pulse::query())
            ->columns([
                TimelineEntry::make()->title('title'),
            ]);

        $table = match ($this->mode) {
            'duplicate-url' => $table
                ->recordActions([
                    ActionGroup::make([
                        Action::make('view')->url(fn (Pulse $record): string => "/pulses/{$record->getKey()}"),
                    ]),
                ])
                ->recordUrl(fn (Pulse $record): string => "/pulses/{$record->getKey()}"),
            'duplicate-action' => $table
                ->recordActions([
                    ActionGroup::make([
                        Action::make('view')->action(fn () => null),
                    ]),
                ])
                ->recordAction('view'),
            'custom-url' => $table
                ->recordActions([
                    Action::make('pin')->action(fn () => null),
                ])
                ->recordUrl(fn (Pulse $record): string => "/pulses/{$record->getKey()}/timeline"),
            'custom-action' => $table
                ->recordActions([
                    Action::make('pin')->action(fn () => null),
                ])
                ->recordAction('inspect'),
        };

        return $table->asTimeline();
    }
}
