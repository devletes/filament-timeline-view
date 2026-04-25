<?php

namespace Devletes\FilamentTimelineView;

use Illuminate\Support\ServiceProvider;

class TimelineViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-timeline-view');

        $this->publishes([
            __DIR__.'/../resources/lang' => $this->app->langPath('vendor/filament-timeline-view'),
        ], 'filament-timeline-view-translations');
    }
}
