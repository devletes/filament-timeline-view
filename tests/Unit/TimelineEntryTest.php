<?php

use Devletes\FilamentTimelineView\Tables\Columns\TimelineEntry;
use Illuminate\Support\Carbon;

beforeEach(function () {
    Carbon::setTestNow('2026-04-25 12:00:00');
});

afterEach(function () {
    Carbon::setTestNow();
});

function renderEntry(TimelineEntry $entry, array $record): string
{
    return $entry->record($record)->toEmbeddedHtml();
}

it('renders the title from a string field path', function () {
    $entry = TimelineEntry::make()->title('subject');

    $html = renderEntry($entry, ['subject' => 'Quarterly review']);

    expect($html)->toContain('Quarterly review');
});

it('renders the title from a Closure', function () {
    $entry = TimelineEntry::make()->title(fn (array $record) => mb_strtoupper($record['subject']));

    $html = renderEntry($entry, ['subject' => 'hello']);

    expect($html)->toContain('HELLO');
});

it('renders content with newlines converted to <br>', function () {
    $entry = TimelineEntry::make()->content('body');

    $html = renderEntry($entry, ['body' => "line one\nline two"]);

    expect($html)->toContain('line one')
        ->and($html)->toContain('<br')
        ->and($html)->toContain('line two');
});

it('renders an image only when the field resolves to a value', function () {
    $entry = TimelineEntry::make()
        ->title('subject')
        ->image('hero_url');

    $withImage = renderEntry($entry, ['subject' => 't', 'hero_url' => 'https://example.com/x.png']);
    $withoutImage = renderEntry($entry, ['subject' => 't', 'hero_url' => null]);

    expect($withImage)->toContain('ftv-card-layout-has-media')
        ->and($withImage)->toContain('https://example.com/x.png');

    expect($withoutImage)->not->toContain('ftv-card-layout-has-media')
        ->and($withoutImage)->not->toContain('<img class="ftv-card-image"');
});

it('renders the author with avatar via mixed string/Closure setters', function () {
    $entry = TimelineEntry::make()->author(
        'author.name',
        fn (array $record) => 'data:image/svg+xml,'.$record['author']['id'],
    );

    $record = [
        'author' => ['name' => 'Test User', 'id' => 42],
    ];

    $html = renderEntry($entry, $record);

    expect($html)->toContain('Test User')
        ->and($html)->toContain('data:image/svg+xml,42');
});

it('formats the time field with the configured format', function () {
    $entry = TimelineEntry::make()->time('published_at', 'H:i');

    $html = renderEntry($entry, ['published_at' => '2026-04-25 14:30:00']);

    expect($html)->toContain('14:30');
});

it('uses a Closure that returns a Carbon instance and formats it', function () {
    $entry = TimelineEntry::make()->time(fn () => Carbon::parse('2026-04-25 09:15:00'), 'g:i A');

    $html = renderEntry($entry, []);

    expect($html)->toContain('9:15 AM');
});

it('uses a Closure that returns a pre-formatted string verbatim', function () {
    $entry = TimelineEntry::make()->time(fn () => 'just now');

    $html = renderEntry($entry, []);

    expect($html)->toContain('just now');
});

it('omits the footer when neither author nor time is configured', function () {
    $entry = TimelineEntry::make()->title('subject');

    $html = renderEntry($entry, ['subject' => 'No footer here']);

    expect($html)->not->toContain('ftv-card-meta');
});

it('omits the title element when title resolves to blank', function () {
    $entry = TimelineEntry::make()->title('subject')->content('body');

    $html = renderEntry($entry, ['subject' => null, 'body' => 'lonely']);

    expect($html)->not->toContain('ftv-card-title')
        ->and($html)->toContain('lonely');
});
