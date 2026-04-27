<?php

namespace Workbench\App\Filament\Widgets;

use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Workbench\App\Models\Pulse;

class CustomLayoutWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Custom column layout')
            ->description('Stack + Split + ImageColumn replacing TimelineEntry — chrome around the card stays the same.')
            ->query(fn () => Pulse::query()->with('author')->whereNotNull('hero_image_url'))
            ->defaultSort('published_at', 'desc')
            ->columns([
                Stack::make([
                    Split::make([
                        ImageColumn::make('hero_image_url')->circular()->size(60)->grow(false),
                        Stack::make([
                            TextColumn::make('title')->weight('bold')->size('lg'),
                            TextColumn::make('body')->color('gray'),
                            Split::make([
                                TextColumn::make('author.name')->size('xs')->color('gray'),
                                TextColumn::make('published_at')->time()->size('xs')->color('gray')->alignEnd(),
                            ]),
                        ]),
                    ]),
                ]),
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
