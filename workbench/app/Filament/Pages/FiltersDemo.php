<?php

namespace Workbench\App\Filament\Pages;

use Filament\Pages\Dashboard;
use Workbench\App\Filament\Widgets\FiltersLayoutWidget;

class FiltersDemo extends Dashboard
{
    protected static ?string $title = 'Filters';

    protected static ?string $navigationLabel = 'Filters';

    protected static ?int $navigationSort = 3;

    protected static string $routePath = '/filters';

    public function getColumns(): int|array
    {
        return 1;
    }

    public function getWidgets(): array
    {
        return [FiltersLayoutWidget::class];
    }
}
