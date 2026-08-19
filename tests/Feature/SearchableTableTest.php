<?php

use Devletes\FilamentTimelineView\Tests\Fixtures\StubTimelineFiltersWidget;
use Devletes\FilamentTimelineView\Tests\Fixtures\StubTimelineSearchWidget;
use Livewire\Livewire;
use Workbench\App\Models\Pulse;

beforeEach(function () {
    Pulse::create(['title' => 'Quarterly review', 'body' => 'Numbers are in.', 'category' => 'news', 'published_at' => now()]);
    Pulse::create(['title' => 'New coffee machine', 'body' => 'In the kitchen.', 'category' => 'news', 'published_at' => now()]);
});

it('renders a search field in the header toolbar when the table is searchable', function () {
    Livewire::test(StubTimelineSearchWidget::class)
        ->assertSeeHtml('fi-ta-header-toolbar')
        ->assertSeeHtml('fi-ta-search-field');
});

it('narrows the rendered records to the search term', function () {
    Livewire::test(StubTimelineSearchWidget::class)
        ->assertSee('Quarterly review')
        ->assertSee('New coffee machine')
        ->set('tableSearch', 'coffee')
        ->assertSee('New coffee machine')
        ->assertDontSee('Quarterly review');
});

it('searches only the columns named in searchable(), not every column', function () {
    Livewire::test(StubTimelineSearchWidget::class)
        ->set('tableSearch', 'kitchen')
        ->assertDontSee('New coffee machine')
        ->assertDontSee('Quarterly review');
});

it('shows the empty state when nothing matches', function () {
    Livewire::test(StubTimelineSearchWidget::class)
        ->set('tableSearch', 'nothing matches this')
        ->assertSeeHtml('fi-ta-empty-state')
        ->assertSeeHtml('fi-ta-search-field');
});

it('renders no search field when the table is not searchable', function () {
    Livewire::test(StubTimelineFiltersWidget::class)
        ->assertDontSeeHtml('fi-ta-search-field');
});

it('renders no filters trigger when the table is only searchable', function () {
    Livewire::test(StubTimelineSearchWidget::class)
        ->assertSeeHtml('fi-ta-search-field')
        ->assertDontSeeHtml('fi-ta-filters-trigger-action-ctn')
        ->assertDontSeeHtml('fi-ta-filters-dropdown');
});
