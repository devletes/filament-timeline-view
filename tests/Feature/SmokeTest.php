<?php

use Devletes\FilamentTimelineView\TimelineViewServiceProvider;

it('boots the package service provider', function () {
    expect(app()->getProvider(TimelineViewServiceProvider::class))
        ->not()->toBeNull();
});
