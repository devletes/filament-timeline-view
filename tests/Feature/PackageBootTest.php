<?php

it('boots the package views', function (): void {
    expect(view()->exists('filament-timeline-view::widgets.timeline-widget'))->toBeTrue();
});
