<?php

namespace Workbench\App\Filament\Widgets;

use Devletes\FilamentTimelineView\Tables\Columns\TimelineEntry;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Notifications\Notification;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Workbench\App\Models\Pulse;

class CompanyPulseDoubleSidedWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Company Pulse — double sided')
            ->description('The same data, rendered with cards alternating either side of the centre line.')
            ->query(fn () => Pulse::query()->with('author'))
            ->defaultSort('published_at', 'desc')
            ->columns([
                TimelineEntry::make()
                    ->title('title')
                    ->content('body')
                    ->image('hero_image_url')
                    ->author('author.name', fn () => '/avatar.png')
                    ->time('published_at'),
            ])
            ->defaultGroup(
                Group::make('published_at')
                    ->date()
                    ->collapsible()
                    ->orderQueryUsing(fn ($query) => $query->orderByDesc('published_at')),
            )
            ->recordActions([
                ActionGroup::make([
                    Action::make('view')
                        ->icon('heroicon-m-eye')
                        ->action(fn (Pulse $record) => Notification::make()
                            ->title("Viewing «{$record->title}»")
                            ->success()
                            ->send()),
                    Action::make('pin')
                        ->icon('heroicon-m-bookmark')
                        ->color('primary')
                        ->action(fn (Pulse $record) => Notification::make()
                            ->title("Pinned «{$record->title}»")
                            ->success()
                            ->send()),
                    Action::make('delete')
                        ->icon('heroicon-m-trash')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(fn (Pulse $record) => Notification::make()
                            ->title("Deleted «{$record->title}»")
                            ->danger()
                            ->send()),
                ])->color('gray'),
            ])
            ->paginated([5])
            ->asDoubleSidedTimeline();
    }
}
