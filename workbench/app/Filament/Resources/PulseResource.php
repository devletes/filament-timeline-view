<?php

namespace Workbench\App\Filament\Resources;

use Devletes\FilamentTimelineView\Tables\Columns\TimelineEntry;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Workbench\App\Filament\Resources\PulseResource\Pages;
use Workbench\App\Models\Pulse;

class PulseResource extends Resource
{
    protected static ?string $model = Pulse::class;

    protected static ?string $navigationLabel = 'Pulses (resource)';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required(),
            TextInput::make('body')->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TimelineEntry::make()
                    ->title('title')
                    ->content('body')
                    ->time('published_at'),
            ])
            ->defaultGroup(Group::make('published_at')->date())
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make(),
                    DeleteAction::make(),
                ]),
            ])
            ->asTimeline();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPulses::route('/'),
            'view' => Pages\ViewPulse::route('/{record}'),
            'edit' => Pages\EditPulse::route('/{record}/edit'),
        ];
    }
}
