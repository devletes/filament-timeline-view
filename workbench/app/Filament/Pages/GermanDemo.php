<?php

namespace Workbench\App\Filament\Pages;

use Filament\Pages\Dashboard;
use Workbench\App\Filament\Widgets\GermanTimelineWidget;

class GermanDemo extends Dashboard
{
    protected static ?string $title = 'German';

    protected static ?string $navigationLabel = 'German';

    protected static ?int $navigationSort = 5;

    protected static string $routePath = '/german';

    public function getColumns(): int|array
    {
        return 1;
    }

    public function getWidgets(): array
    {
        return [GermanTimelineWidget::class];
    }

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        app()->setLocale('de');

        return parent::getTitle();
    }
}
