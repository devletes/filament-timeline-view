<?php

use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Workbench\App\Filament\Resources\PulseResource\Pages\ListPulses;
use Workbench\App\Models\Pulse;
use Workbench\App\Models\User;

beforeEach(function () {
    Filament::setCurrentPanel('admin');

    $this->actingAs(User::create([
        'name' => 'Tester',
        'email' => 'tester@example.com',
        'password' => bcrypt('password'),
    ]));

    Pulse::create([
        'title' => 'Quarterly update',
        'body' => 'Everything is on track.',
        'published_at' => now(),
    ]);
});

it('renders the timeline on a resource list page', function () {
    Livewire::test(ListPulses::class)
        ->assertOk()
        ->assertSee('Quarterly update')
        ->assertSeeHtml('fi-ta-timeline');
});

it('renders the view action once, not alongside a derived card view link', function () {
    Livewire::test(ListPulses::class)
        ->assertDontSeeHtml('ftv-card-view-link');
});

it('derives a record URL from the resource view page', function () {
    $recordUrl = Livewire::test(ListPulses::class)
        ->instance()
        ->getTable()
        ->getRecordUrl(Pulse::first());

    expect($recordUrl)->toContain('/admin/pulses/'.Pulse::first()->getKey());
});

it('exposes the view and edit actions from the card kebab', function (string $action) {
    Livewire::test(ListPulses::class)
        ->assertActionVisible(TestAction::make($action)->table(Pulse::first()));
})->with(['view', 'edit', 'delete']);

it('mounts the view action from the card kebab', function () {
    Livewire::test(ListPulses::class)
        ->mountAction(TestAction::make('view')->table(Pulse::first()))
        ->assertHasNoActionErrors();
});
