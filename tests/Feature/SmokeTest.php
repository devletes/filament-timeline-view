<?php

it('boots the package service provider', function () {
    expect(app()->getProvider(\Devletes\FilamentTimelineView\TimelineViewServiceProvider::class))
        ->not()->toBeNull();
});
