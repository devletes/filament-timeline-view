<?php

namespace Devletes\FilamentTimelineView;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TimelineViewServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-timeline-view';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasViews();
    }

    public function packageBooted(): void
    {
        FilamentAsset::register([
            Css::make('timeline-widget', __DIR__.'/../resources/css/timeline-widget.css'),
        ], 'devletes/filament-timeline-view');
    }
}
