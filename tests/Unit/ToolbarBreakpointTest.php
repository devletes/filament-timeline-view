<?php

function breakpointFor(string $css, string $selector): ?string
{
    $pattern = '/@media\s*\(min-width:\s*([0-9.]+rem)\s*\)\s*\{[^{]*'.preg_quote($selector, '/').'/';

    return preg_match($pattern, $css, $matches) ? $matches[1] : null;
}

// The toolbar strip must disappear at exactly the breakpoint Filament hides the
// trigger it contains, otherwise an empty bordered row reappears in between.
it('hides the toolbar at the same breakpoint Filament hides the filters trigger', function () {
    $filamentCss = file_get_contents(__DIR__.'/../../vendor/filament/filament/dist/theme.css');
    $packageCss = file_get_contents(__DIR__.'/../../resources/css/timeline-widget.css');

    $filamentBreakpoint = breakpointFor($filamentCss, '.fi-ta-filters-trigger-action-ctn.lg\\:fi-hidden');
    $packageBreakpoint = breakpointFor($packageCss, '.ftv-header-toolbar-lg-empty');

    expect($filamentBreakpoint)->not()->toBeNull('Filament no longer ships the lg:fi-hidden rule for the filters trigger');
    expect($packageBreakpoint)->not()->toBeNull('the package no longer ships the toolbar rule');
    expect($packageBreakpoint)->toBe($filamentBreakpoint);
});

it('ships the compiled toolbar rule in dist', function () {
    $dist = file_get_contents(__DIR__.'/../../dist/timeline-widget.css');

    expect($dist)->toContain('ftv-header-toolbar-lg-empty');
});
