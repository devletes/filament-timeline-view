<?php

namespace Workbench\App\Filament\Pages;

use Filament\Pages\Dashboard;
use Workbench\App\Filament\Widgets\CompanyPulseWidget;

class SingleSidedDemo extends Dashboard
{
    protected static ?string $title = 'Single-sided';

    protected static ?string $navigationLabel = 'Single-sided';

    protected static ?int $navigationSort = 1;

    protected static string $routePath = '/single-sided';

    public function getColumns(): int|array
    {
        return 1;
    }

    public function getWidgets(): array
    {
        return [CompanyPulseWidget::class];
    }
}
