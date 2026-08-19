@php
    $filtersSidebarClass = "fi-ta-filters-{$position}-content";
@endphp

<div
    wire:ignore.self
    x-ref="filtersContentContainer"
    x-transition:enter-start="fi-opacity-0"
    x-transition:leave-end="fi-opacity-0"
    x-bind:class="{ 'fi-open': areFiltersOpen }"
    @class([
        "{$filtersSidebarClass}-ctn",
        'lg:fi-open' => ! $hasCollapsibleFilters,
        $filtersSidebarWidthClass,
    ])
>
    <x-filament-tables::filters
        :apply-action="$filtersApplyAction"
        :form="$filtersForm"
        :heading-tag="$secondLevelHeadingTag"
        :class="$filtersSidebarClass"
        :reset-action-position="$filtersResetActionPosition"
    />
</div>
