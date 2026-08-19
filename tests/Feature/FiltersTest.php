<?php

use Devletes\FilamentTimelineView\Tests\Fixtures\StubTimelineFiltersWidget;
use Livewire\Livewire;
use Workbench\App\Models\Pulse;

beforeEach(function () {
    Pulse::create([
        'title' => 'Release 2.0 is out',
        'body' => 'Shipped today.',
        'category' => 'release',
        'published_at' => now(),
    ]);

    Pulse::create([
        'title' => 'We hired a designer',
        'body' => 'Welcome aboard.',
        'category' => 'news',
        'published_at' => now(),
    ]);
});

it('renders the filters trigger in the header toolbar', function () {
    Livewire::test(StubTimelineFiltersWidget::class)
        ->assertSeeHtml('fi-ta-header-toolbar')
        ->assertSeeHtml('fi-ta-filters-dropdown');
});

it('applies filter state to the rendered records', function () {
    Livewire::test(StubTimelineFiltersWidget::class)
        ->assertSee('Release 2.0 is out')
        ->assertSee('We hired a designer')
        ->set('tableFilters.category.value', 'release')
        ->assertSee('Release 2.0 is out')
        ->assertDontSee('We hired a designer');
});

it('renders removable filter indicators once a filter is active', function () {
    Livewire::test(StubTimelineFiltersWidget::class)
        ->assertDontSeeHtml('fi-ta-filter-indicators')
        ->set('tableFilters.category.value', 'release')
        ->assertSeeHtml('fi-ta-filter-indicators')
        ->assertSee('Category: Release');
});

it('renders every filters layout', function (string $layout, string $expectedHtml) {
    Livewire::test(StubTimelineFiltersWidget::class, ['layout' => $layout])
        ->assertSeeHtml($expectedHtml);
})->with([
    ['dropdown', 'fi-ta-filters-dropdown'],
    ['modal', 'fi-ta-filters-modal'],
    ['above', 'fi-ta-filters-above-content-ctn'],
    ['above-collapsible', 'fi-ta-filters-above-content-ctn'],
    ['below', 'fi-ta-filters-below-content'],
    ['before', 'fi-ta-filters-before-content-ctn'],
    ['before-collapsible', 'fi-ta-filters-before-content-ctn'],
    ['after', 'fi-ta-filters-after-content-ctn'],
    ['after-collapsible', 'fi-ta-filters-after-content-ctn'],
]);

it('compiles the filters panel for every layout that renders one', function (string $layout) {
    Livewire::test(StubTimelineFiltersWidget::class, ['layout' => $layout])
        ->assertDontSeeHtml('<x-filament-tables::filters')
        ->assertSeeHtml('fi-ta-filters-heading');
})->with([
    'dropdown',
    'above',
    'above-collapsible',
    'below',
    'before',
    'before-collapsible',
    'after',
    'after-collapsible',
]);

it('renders no filter UI at all for the hidden layout', function () {
    $html = Livewire::test(StubTimelineFiltersWidget::class, ['layout' => 'hidden'])->html();

    expect($html)
        ->not()->toContain('fi-ta-header-toolbar')
        ->not()->toContain('fi-ta-filters');
});

it('collapses the empty toolbar strip for the non-collapsible sidebar layouts', function (string $layout) {
    Livewire::test(StubTimelineFiltersWidget::class, ['layout' => $layout])
        ->assertSeeHtml('ftv-header-toolbar-lg-empty');
})->with(['before', 'after']);

it('keeps the toolbar for layouts whose trigger stays visible', function (string $layout) {
    Livewire::test(StubTimelineFiltersWidget::class, ['layout' => $layout])
        ->assertSeeHtml('fi-ta-header-toolbar')
        ->assertDontSeeHtml('ftv-header-toolbar-lg-empty');
})->with(['dropdown', 'modal', 'before-collapsible', 'after-collapsible']);
