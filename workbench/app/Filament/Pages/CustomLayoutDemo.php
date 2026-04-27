<?php

namespace Workbench\App\Filament\Pages;

use Filament\Pages\Dashboard;
use Workbench\App\Filament\Widgets\CustomLayoutWidget;

class CustomLayoutDemo extends Dashboard
{
    protected static ?string $title = 'Custom layout';

    protected static ?string $navigationLabel = 'Custom layout';

    protected static ?int $navigationSort = 3;

    protected static string $routePath = '/custom-layout';

    public function getColumns(): int|array
    {
        return 1;
    }

    public function getWidgets(): array
    {
        return [CustomLayoutWidget::class];
    }
}
