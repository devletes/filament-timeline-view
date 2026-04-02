<?php

namespace Devletes\FilamentTimelineView\Schemas\Components;

use Devletes\FilamentTimelineView\Concerns\HasTimeline;
use Filament\Schemas\Components\Component;

class Timeline extends Component
{
    use HasTimeline;

    protected string $view = 'filament-timeline-view::schemas.components.timeline';

    public static function make(?string $name = null): static
    {
        $static = app(static::class);
        $static->configure();

        if (filled($name)) {
            $static->key($name);
            $static->statePath($name);
        }

        return $static;
    }
}
