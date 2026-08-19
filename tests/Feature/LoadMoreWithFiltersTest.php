<?php

use Devletes\FilamentTimelineView\Tests\Fixtures\StubTimelinePaginatedFiltersWidget;
use Livewire\Livewire;
use Workbench\App\Models\Pulse;

beforeEach(function () {
    foreach ([
        ['News one', 'news'],
        ['News two', 'news'],
        ['News three', 'news'],
        ['Release one', 'release'],
        ['Release two', 'release'],
    ] as [$title, $category]) {
        Pulse::create([
            'title' => $title,
            'body' => 'Body.',
            'category' => $category,
            'published_at' => now(),
        ]);
    }
});

it('shows the load more button while pages remain', function () {
    Livewire::test(StubTimelinePaginatedFiltersWidget::class)
        ->assertSee('News one')
        ->assertSee('News two')
        ->assertDontSee('News three')
        ->assertSeeHtml('ftv-pagination-load-more');
});

it('hides the load more button once every record is loaded', function () {
    Livewire::test(StubTimelinePaginatedFiltersWidget::class)
        ->set('tableRecordsPerPage', 10)
        ->assertSee('News three')
        ->assertSee('Release two')
        ->assertDontSeeHtml('ftv-pagination-load-more');
});

it('filters correctly after the page size has been increased', function () {
    Livewire::test(StubTimelinePaginatedFiltersWidget::class)
        ->set('tableRecordsPerPage', 4)
        ->assertSee('Release one')
        ->set('tableFilters.category.value', 'news')
        ->assertSee('News one')
        ->assertSee('News two')
        ->assertSee('News three')
        ->assertDontSee('Release one')
        ->assertDontSee('Release two');
});

it('keeps the load more button when a filter still leaves more pages', function () {
    Livewire::test(StubTimelinePaginatedFiltersWidget::class)
        ->set('tableFilters.category.value', 'news')
        ->assertSee('News one')
        ->assertSee('News two')
        ->assertDontSee('News three')
        ->assertSeeHtml('ftv-pagination-load-more');
});

it('drops the load more button when a filter leaves a single page', function () {
    Livewire::test(StubTimelinePaginatedFiltersWidget::class)
        ->set('tableFilters.category.value', 'release')
        ->assertSee('Release one')
        ->assertSee('Release two')
        ->assertDontSeeHtml('ftv-pagination-load-more');
});

it('loads more within a filtered result set', function () {
    Livewire::test(StubTimelinePaginatedFiltersWidget::class)
        ->set('tableFilters.category.value', 'news')
        ->assertDontSee('News three')
        ->set('tableRecordsPerPage', 4)
        ->assertSee('News three')
        ->assertDontSee('Release one');
});

it('returns to the first page when a filter is applied from a later page', function () {
    Livewire::test(StubTimelinePaginatedFiltersWidget::class)
        ->set('tableRecordsPerPage', 2)
        ->call('nextPage')
        ->set('tableFilters.category.value', 'news')
        ->assertSee('News one')
        ->assertSee('News two');
});
