<?php

namespace Workbench\App\Filament\Pages;

use Filament\Pages\Dashboard;
use Workbench\App\Filament\Widgets\CompanyPulseDoubleSidedWidget;

class DoubleSidedDemo extends Dashboard
{
    protected static ?string $title = 'Double-sided';

    protected static ?string $navigationLabel = 'Double-sided';

    protected static ?int $navigationSort = 2;

    protected static string $routePath = '/double-sided';

    public function getColumns(): int|array
    {
        return 1;
    }

    public function getWidgets(): array
    {
        return [CompanyPulseDoubleSidedWidget::class];
    }
}
