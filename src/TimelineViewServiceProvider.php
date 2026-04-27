<?php

namespace Devletes\FilamentTimelineView;

use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Table;
use Illuminate\Support\ServiceProvider;

class TimelineViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-timeline-view');
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-timeline-view');

        $this->publishes([
            __DIR__.'/../resources/lang' => $this->app->langPath('vendor/filament-timeline-view'),
        ], 'filament-timeline-view-translations');

        FilamentAsset::register([
            Css::make('timeline-view', __DIR__.'/../dist/timeline-widget.css'),
        ], package: 'devletes/filament-timeline-view');

        $this->registerTableMacros();
    }

    protected function registerTableMacros(): void
    {
        Table::macro('asTimeline', function (): Table {
            return $this->view('filament-timeline-view::tables.timeline', [
                'timelineLayout' => 'single',
            ]);
        });

        Table::macro('asDoubleSidedTimeline', function (): Table {
            return $this->view('filament-timeline-view::tables.timeline', [
                'timelineLayout' => 'double',
            ]);
        });
    }
}
