<?php

namespace Workbench\App\Filament\Pages;

use Filament\Pages\Dashboard;
use Workbench\App\Filament\Widgets\ThemedTimelineWidget;

class ThemedDemo extends Dashboard
{
    protected static ?string $title = 'Themed';

    protected static ?string $navigationLabel = 'Themed';

    protected static ?int $navigationSort = 4;

    protected static string $routePath = '/themed';

    public function getColumns(): int|array
    {
        return 1;
    }

    public function getWidgets(): array
    {
        return [ThemedTimelineWidget::class];
    }
}
