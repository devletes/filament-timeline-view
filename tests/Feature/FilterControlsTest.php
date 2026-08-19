<?php

use Devletes\FilamentTimelineView\Tests\Fixtures\StubTimelineFiltersWidget;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
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

it('applies deferred filters only once the apply action runs', function () {
    Livewire::test(StubTimelineFiltersWidget::class)
        ->set('tableFilters.category.value', 'release')
        ->call('applyTableFilters')
        ->assertSee('Release 2.0 is out')
        ->assertDontSee('We hired a designer');
});

it('clears the filter through the reset action', function () {
    Livewire::test(StubTimelineFiltersWidget::class)
        ->set('tableFilters.category.value', 'release')
        ->assertDontSee('We hired a designer')
        ->call('resetTableFiltersForm')
        ->assertSee('Release 2.0 is out')
        ->assertSee('We hired a designer')
        ->assertDontSeeHtml('fi-ta-filter-indicators');
});

it('clears the filter through an indicator badge remove button', function () {
    Livewire::test(StubTimelineFiltersWidget::class)
        ->set('tableFilters.category.value', 'release')
        ->assertSeeHtml('fi-ta-filter-indicators')
        ->call('removeTableFilter', 'category', 'value')
        ->assertSee('We hired a designer')
        ->assertDontSeeHtml('fi-ta-filter-indicators');
});

it('clears every filter through the remove all action', function () {
    Livewire::test(StubTimelineFiltersWidget::class)
        ->set('tableFilters.category.value', 'release')
        ->assertSeeHtml('fi-ta-filter-indicators')
        ->call('removeTableFilters')
        ->assertSee('Release 2.0 is out')
        ->assertSee('We hired a designer')
        ->assertDontSeeHtml('fi-ta-filter-indicators');
});

it('renders the remove all action only while a removable indicator exists', function () {
    Livewire::test(StubTimelineFiltersWidget::class)
        ->assertDontSeeHtml('wire:click="removeTableFilters"')
        ->set('tableFilters.category.value', 'release')
        ->assertSeeHtml('removeTableFilters');
});

it('lets a render hook replace the filter indicators block', function () {
    FilamentView::registerRenderHook(
        TablesRenderHook::FILTER_INDICATORS,
        fn (array $scopes = [], array $filterIndicators = []): string => '<div class="custom-indicators">'.count($filterIndicators).' active</div>',
    );

    Livewire::test(StubTimelineFiltersWidget::class)
        ->set('tableFilters.category.value', 'release')
        ->assertSeeHtml('custom-indicators')
        ->assertDontSeeHtml('fi-ta-filter-indicators');
});
