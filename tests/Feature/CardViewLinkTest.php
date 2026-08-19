<?php

use Devletes\FilamentTimelineView\Tests\Fixtures\StubTimelineActionsWidget;
use Livewire\Livewire;
use Workbench\App\Models\Pulse;

beforeEach(function () {
    Pulse::create([
        'title' => 'Quarterly update',
        'body' => 'Everything is on track.',
        'published_at' => now(),
    ]);
});

it('does not render the card view link when a record action already links to the record URL', function () {
    Livewire::test(StubTimelineActionsWidget::class, ['mode' => 'duplicate-url'])
        ->assertDontSeeHtml('ftv-card-view-link');
});

it('does not render the card view link when a record action mounts the record action', function () {
    Livewire::test(StubTimelineActionsWidget::class, ['mode' => 'duplicate-action'])
        ->assertDontSeeHtml('ftv-card-view-link');
});

it('renders the card view link for a record URL that no card action points at', function () {
    Livewire::test(StubTimelineActionsWidget::class, ['mode' => 'custom-url'])
        ->assertSeeHtml('ftv-card-view-link');
});

it('renders the card view link for a record action that no card action mounts', function () {
    Livewire::test(StubTimelineActionsWidget::class, ['mode' => 'custom-action'])
        ->assertSeeHtml('ftv-card-view-link');
});
