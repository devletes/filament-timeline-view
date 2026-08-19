@php
    use Carbon\CarbonInterface;
    use Filament\Support\Enums\Width;
    use Filament\Support\Facades\FilamentView;
    use Filament\Tables\Enums\FiltersLayout;
    use Filament\Tables\View\TablesRenderHook;
    use Illuminate\Support\Carbon;

    $records = $isLoaded ? $getRecords() : null;
    $columnsLayout = $getColumnsLayout();
    $group = $getGrouping();
    $heading = $getHeading();
    $description = $getDescription();
    $headerActions = array_filter(
        $getHeaderActions(),
        fn (\Filament\Actions\Action | \Filament\Actions\ActionGroup $action): bool => $action->isVisible(),
    );
    $headerActionsPosition = $getHeaderActionsPosition();

    $isGlobalSearchVisible = $isSearchable();

    $hasFilters = $isFilterable();
    $activeFiltersCount = $getActiveFiltersCount();
    $filterIndicators = $getFilterIndicators();
    $filtersApplyAction = $getFiltersApplyAction();
    $filtersForm = $getFiltersForm();
    $filtersFormMaxHeight = $getFiltersFormMaxHeight();
    $filtersFormWidth = $getFiltersFormWidth();
    $filtersLayout = $getFiltersLayout();
    $filtersResetActionPosition = $getFiltersResetActionPosition();
    $filtersTriggerAction = $getFiltersTriggerAction();
    $hasFiltersDialog = $hasFilters && in_array($filtersLayout, [FiltersLayout::Dropdown, FiltersLayout::Modal]);
    $hasFiltersAboveContent = $hasFilters && in_array($filtersLayout, [FiltersLayout::AboveContent, FiltersLayout::AboveContentCollapsible]);
    $hasFiltersBelowContent = $hasFilters && ($filtersLayout === FiltersLayout::BelowContent);
    $hasFiltersBeforeContent = $hasFilters && in_array($filtersLayout, [FiltersLayout::BeforeContent, FiltersLayout::BeforeContentCollapsible]);
    $hasFiltersAfterContent = $hasFilters && in_array($filtersLayout, [FiltersLayout::AfterContent, FiltersLayout::AfterContentCollapsible]);
    $hasCollapsibleFilters = $hasFilters && in_array($filtersLayout, [FiltersLayout::AboveContentCollapsible, FiltersLayout::BeforeContentCollapsible, FiltersLayout::AfterContentCollapsible]);
    $hasFiltersSidebar = $hasFiltersBeforeContent || $hasFiltersAfterContent;
    $hasFiltersTrigger = $hasFiltersDialog || $hasFiltersSidebar;
    // Sidebar layouts hide their trigger from `lg` up, leaving the toolbar empty unless search fills it.
    $hasToolbarEmptyOnLargeScreens = $hasFiltersSidebar && (! $hasCollapsibleFilters) && (! $isGlobalSearchVisible);

    if (is_string($filtersFormWidth)) {
        $filtersFormWidth = Width::tryFrom($filtersFormWidth) ?? $filtersFormWidth;
    }

    $filtersSidebarWidth = $filtersFormWidth ?? Width::ExtraSmall;
    $filtersSidebarWidthClass = $filtersSidebarWidth instanceof Width ? "fi-width-{$filtersSidebarWidth->value}" : $filtersSidebarWidth;

    $hasHeaderContent = filled($heading) || filled($description) || filled($headerActions) || $hasFiltersAboveContent || $hasFiltersTrigger || $isGlobalSearchVisible;
    $hasHeader = $hasHeaderContent || $hasFilters;
    $hasEmptyState = ($records !== null) && (! count($records));
    $hasPagination = ($records instanceof \Illuminate\Contracts\Pagination\Paginator)
        || ($records instanceof \Illuminate\Contracts\Pagination\CursorPaginator);
    $isDouble = ($timelineLayout ?? 'single') === 'double';

    $loadMoreStep = (int) (collect($getPaginationPageOptions())->first(fn ($option) => is_int($option)) ?? 5);
    $currentPerPage = $records instanceof \Illuminate\Contracts\Pagination\Paginator ? (int) $records->perPage() : null;
    $loadMoreNextPerPage = ($currentPerPage !== null) ? ($currentPerPage + $loadMoreStep) : null;
    $hasMorePages = $hasPagination && method_exists($records, 'hasMorePages') && $records->hasMorePages();
    $hasFooter = $hasMorePages || $hasFiltersBelowContent;

    $groupedRecords = [];

    if (($records !== null) && count($records)) {
        foreach ($records as $record) {
            $groupKey = $group?->getStringKey($record) ?? '__none__';

            if (! isset($groupedRecords[$groupKey])) {
                $groupedRecords[$groupKey] = [
                    'title' => $group?->getTitle($record),
                    'records' => [],
                    'firstRecord' => $record,
                ];
            }

            $groupedRecords[$groupKey]['records'][] = $record;
        }
    }

    $totalGroups = count($groupedRecords);
    $groupIterationIndex = 0;

    $headingTag = $getHeadingTag();
    $secondLevelHeadingTag = $heading ? $getHeadingTag(1) : $headingTag;
@endphp

<div @class([
        'fi-ta-ctn',
        'fi-ta-ctn-with-header' => $hasHeader,
        'fi-ta-ctn-with-footer' => $hasFooter,
    ])
    @if ($hasFiltersSidebar || $hasCollapsibleFilters)
        x-data="filamentTable({
                    areGroupsCollapsedByDefault: @js($areGroupsCollapsedByDefault()),
                    canTrackDeselectedRecords: @js($canTrackDeselectedRecords()),
                    currentSelectionLivewireProperty: @js($getCurrentSelectionLivewireProperty()),
                    maxSelectableRecords: @js($getMaxSelectableRecords()),
                    selectsCurrentPageOnly: @js($selectsCurrentPageOnly()),
                    $wire,
                })"
    @endif
>
    @if ($hasFiltersBeforeContent)
        @include('filament-timeline-view::tables.partials.filters-sidebar', ['position' => 'before'])
    @endif

    <div class="fi-ta-main">
        @if ($hasHeaderContent)
            <header class="fi-ta-header-ctn">
                @if (filled($heading) || filled($description) || filled($headerActions))
                    <div @class([
                            'fi-ta-header',
                            'fi-ta-header-adaptive-actions-position' => $headerActions && ($headerActionsPosition === \Filament\Tables\Actions\HeaderActionsPosition::Adaptive),
                        ])>
                        @if (filled($heading) || filled($description))
                            <div class="fi-ta-header-text-ctn">
                                @if (filled($heading))
                                    <h3 class="fi-ta-header-heading">{{ $heading }}</h3>
                                @endif
                                @if (filled($description))
                                    <p class="fi-ta-header-description">{{ $description }}</p>
                                @endif
                            </div>
                        @endif

                        @if (filled($headerActions))
                            <div class="fi-ta-actions fi-align-start fi-wrapped">
                                @foreach ($headerActions as $action)
                                    {{ $action }}
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif

                @if ($hasFiltersAboveContent)
                    <div
                        @if ($hasCollapsibleFilters)
                            x-bind:class="{ 'fi-open': areFiltersOpen }"
                        @endif
                        class="fi-ta-filters-above-content-ctn"
                    >
                        <x-filament-tables::filters
                            :apply-action="$filtersApplyAction"
                            :form="$filtersForm"
                            :heading-tag="$secondLevelHeadingTag"
                            x-cloak
                            :x-show="$hasCollapsibleFilters ? 'areFiltersOpen' : null"
                            :reset-action-position="$filtersResetActionPosition"
                        />

                        @if ($hasCollapsibleFilters)
                            <span
                                x-on:click="areFiltersOpen = ! areFiltersOpen"
                                class="fi-ta-filters-trigger-action-ctn"
                            >
                                {{ $filtersTriggerAction->badge($activeFiltersCount) }}
                            </span>
                        @endif
                    </div>
                @endif

                @if ($hasFiltersTrigger || $isGlobalSearchVisible)
                    <div @class([
                            'fi-ta-header-toolbar',
                            'ftv-header-toolbar-lg-empty' => $hasToolbarEmptyOnLargeScreens,
                        ])>
                        <div class="fi-ta-actions fi-align-start fi-wrapped"></div>

                        <div>
                            @if ($isGlobalSearchVisible)
                                <x-filament-tables::search-field
                                    :debounce="$getSearchDebounce()"
                                    :on-blur="$isSearchOnBlur()"
                                    :placeholder="$getSearchPlaceholder()"
                                />
                            @endif

                            @if ($hasFiltersDialog)
                                @if (($filtersLayout === FiltersLayout::Modal) || $filtersTriggerAction->isModalSlideOver())
                                    <x-filament::modal
                                        :alignment="$filtersTriggerAction->getModalAlignment()"
                                        :autofocus="$filtersTriggerAction->isModalAutofocused()"
                                        :close-button="$filtersTriggerAction->hasModalCloseButton()"
                                        :close-by-clicking-away="$filtersTriggerAction->isModalClosedByClickingAway()"
                                        :close-by-escaping="$filtersTriggerAction->isModalClosedByEscaping()"
                                        :description="$filtersTriggerAction->getModalDescription()"
                                        :footer-actions="$filtersTriggerAction->getVisibleModalFooterActions()"
                                        :footer-actions-alignment="$filtersTriggerAction->getModalFooterActionsAlignment()"
                                        :heading="$filtersTriggerAction->getCustomModalHeading() ?? __('filament-tables::table.filters.heading')"
                                        :icon="$filtersTriggerAction->getModalIcon()"
                                        :icon-color="$filtersTriggerAction->getModalIconColor()"
                                        :slide-over="$filtersTriggerAction->isModalSlideOver()"
                                        :sticky-footer="$filtersTriggerAction->isModalFooterSticky()"
                                        :sticky-header="$filtersTriggerAction->isModalHeaderSticky()"
                                        :width="$filtersFormWidth"
                                        :wire:key="$this->getId() . '.table.filters'"
                                        class="fi-ta-filters-modal"
                                    >
                                        <x-slot name="trigger">
                                            {{ $filtersTriggerAction->badge($activeFiltersCount) }}
                                        </x-slot>

                                        {{ $filtersTriggerAction->getModalContent() }}

                                        {{ $filtersForm }}

                                        {{ $filtersTriggerAction->getModalContentFooter() }}
                                    </x-filament::modal>
                                @else
                                    <x-filament::dropdown
                                        :max-height="$filtersFormMaxHeight"
                                        placement="bottom-end"
                                        shift
                                        :flip="false"
                                        :width="$filtersFormWidth ?? Width::ExtraSmall"
                                        :wire:key="$this->getId() . '.table.filters'"
                                        class="fi-ta-filters-dropdown"
                                    >
                                        <x-slot name="trigger">
                                            {{ $filtersTriggerAction->badge($activeFiltersCount) }}
                                        </x-slot>

                                        <x-filament-tables::filters
                                            :apply-action="$filtersApplyAction"
                                            :form="$filtersForm"
                                            :heading-tag="$secondLevelHeadingTag"
                                            :reset-action-position="$filtersResetActionPosition"
                                        />
                                    </x-filament::dropdown>
                                @endif
                            @elseif ($hasFiltersSidebar)
                                <span
                                    x-ref="filtersTriggerActionContainer"
                                    x-on:click="toggleFiltersDropdown"
                                    @class([
                                        'fi-ta-filters-trigger-action-ctn',
                                        'lg:fi-hidden' => ! $hasCollapsibleFilters,
                                    ])
                                >
                                    {{ $filtersTriggerAction->badge($activeFiltersCount) }}
                                </span>
                            @endif
                        </div>
                    </div>
                @endif
            </header>
        @endif

        @if (filled($filterIndicators))
            @if (filled($filterIndicatorsView = FilamentView::renderHook(TablesRenderHook::FILTER_INDICATORS, scopes: $this::class, data: ['filterIndicators' => $filterIndicators])))
                {{ $filterIndicatorsView }}
            @else
                <div class="fi-ta-filter-indicators">
                    <div>
                        <span class="fi-ta-filter-indicators-label">
                            {{ __('filament-tables::table.filters.indicator') }}
                        </span>

                        <div class="fi-ta-filter-indicators-badges-ctn">
                            @foreach ($filterIndicators as $indicator)
                                <x-filament::badge :color="$indicator->getColor()">
                                    {{ $indicator->getLabel() }}

                                    @if ($indicator->isRemovable())
                                        <x-slot
                                            name="deleteButton"
                                            :label="__('filament-tables::table.filters.actions.remove.label')"
                                            :wire:click="$indicator->getRemoveLivewireClickHandler()"
                                            wire:loading.attr="disabled"
                                            wire:target="removeTableFilter"
                                        ></x-slot>
                                    @endif
                                </x-filament::badge>
                            @endforeach
                        </div>
                    </div>

                    @if (collect($filterIndicators)->contains(fn (\Filament\Tables\Filters\Indicator $indicator): bool => $indicator->isRemovable()))
                        {{ $getFiltersRemoveAllAction() }}
                    @endif
                </div>
            @endif
        @endif

        <div class="fi-ta-content-ctn">
            @if ($hasEmptyState)
                @if ($emptyState = $getEmptyState())
                    {{ $emptyState }}
                @else
                    <div class="fi-ta-empty-state">
                        <div class="fi-ta-empty-state-content">
                            <div class="fi-ta-empty-state-icon-bg">
                                {{ \Filament\Support\generate_icon_html($getEmptyStateIcon(), size: \Filament\Support\Enums\IconSize::Large) }}
                            </div>

                            <{{ $secondLevelHeadingTag }} class="fi-ta-empty-state-heading">
                                {{ $getEmptyStateHeading() }}
                            </{{ $secondLevelHeadingTag }}>

                            @if (filled($emptyStateDescription = $getEmptyStateDescription()))
                                <p class="fi-ta-empty-state-description">
                                    {{ $emptyStateDescription }}
                                </p>
                            @endif

                            @if ($emptyStateActions = array_filter(
                                     $getEmptyStateActions(),
                                     fn (\Filament\Actions\Action | \Filament\Actions\ActionGroup $action): bool => $action->isVisible(),
                                 ))
                                <div class="fi-ta-actions fi-align-center fi-wrapped">
                                    @foreach ($emptyStateActions as $action)
                                        {{ $action }}
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            @elseif ($records !== null)
                <div @class([
                        'fi-ta-timeline',
                        'fi-ta-timeline-double' => $isDouble,
                    ])
                    wire:key="{{ $this->getId() }}.table.timeline"
                >
                    <div @class([
                            'ftv-shell',
                            'ftv-double-sided' => $isDouble,
                        ])>
                        <div class="ftv-root">
                            @foreach ($groupedRecords as $groupKey => $bucket)
                                @php
                                    $groupIterationIndex++;
                                    $isLastGroup = $groupIterationIndex === $totalGroups;
                                    $bucketRecords = $bucket['records'];
                                    $bucketTitle = $bucket['title'];
                                    $firstRecord = $bucket['firstRecord'];
                                    $bucketCount = count($bucketRecords);
                                    $toggleId = 'ftv-toggle-'.md5($groupKey.$groupIterationIndex);

                                    $datePrimary = $bucketTitle;
                                    $dateSecondary = null;

                                    if ($group?->isDate()) {
                                        $hasCustomTitleResolver = false;

                                        if ($group !== null) {
                                            $titleResolver = (new \ReflectionProperty(\Filament\Tables\Grouping\Group::class, 'getTitleFromRecordUsing'))->getValue($group);
                                            $hasCustomTitleResolver = $titleResolver !== null;
                                        }

                                        if (! $hasCustomTitleResolver) {
                                            $rawValue = data_get($firstRecord, $group->getColumn());

                                            if (filled($rawValue)) {
                                                $date = $rawValue instanceof CarbonInterface ? $rawValue : Carbon::parse($rawValue);

                                                if ($date->isToday()) {
                                                    $datePrimary = __('filament-timeline-view::timeline.today');
                                                } else {
                                                    $datePrimary = $date->translatedFormat('l');
                                                }

                                                $datePartial = $date->translatedFormat('M j');
                                                $dateSecondary = ($date->year !== now()->year)
                                                    ? "{$datePartial}, {$date->year}"
                                                    : $datePartial;
                                            }
                                        }
                                    }
                                @endphp

                                @php
                                    $isGroupCollapsible = (bool) $group?->isCollapsible();
                                @endphp

                                <div @class([
                                        'ftv-group',
                                        'ftv-group-collapsible' => $isGroupCollapsible,
                                        'ftv-group-last' => $isLastGroup,
                                    ])
                                    @if ($isGroupCollapsible)
                                        x-data="{ collapsed: false }"
                                        x-bind:class="{ 'ftv-group-collapsed': collapsed }"
                                    @endif
                                >
                                    <div class="ftv-date-row">
                                        <div class="ftv-date-button">
                                            <span class="ftv-date-primary">{{ $datePrimary }}</span>
                                            @if (filled($dateSecondary))
                                                <span class="ftv-date-secondary">{{ $dateSecondary }}</span>
                                            @endif
                                        </div>

                                        @if ($isGroupCollapsible)
                                            <button
                                                type="button"
                                                class="ftv-date-toggle"
                                                aria-controls="{{ $toggleId }}"
                                                x-bind:aria-expanded="(! collapsed).toString()"
                                                x-on:click="collapsed = ! collapsed"
                                                aria-label="{{ __('filament-timeline-view::timeline.toggle_day', ['day' => $datePrimary]) }}"
                                            >
                                                <svg
                                                    class="ftv-date-toggle-icon"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                    aria-hidden="true"
                                                >
                                                    <path fill-rule="evenodd" d="M14.78 11.78a.75.75 0 0 1-1.06 0L10 8.06l-3.72 3.72a.75.75 0 1 1-1.06-1.06l4.25-4.25a.75.75 0 0 1 1.06 0l4.25 4.25a.75.75 0 0 1 0 1.06Z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                        @endif
                                    </div>

                                    <div class="ftv-group-body ftv-group-body-has-line" id="{{ $toggleId }}">
                                        <div @if ($isGroupCollapsible) x-show="! collapsed" @endif>
                                            @if ($isDouble)
                                                @php
                                                    $leftItems = [];
                                                    $rightItems = [];

                                                    foreach ($bucketRecords as $bucketIndex => $bucketRecord) {
                                                        $bucketIndex % 2 === 0
                                                            ? $leftItems[] = ['record' => $bucketRecord, 'order' => $bucketIndex]
                                                            : $rightItems[] = ['record' => $bucketRecord, 'order' => $bucketIndex];
                                                    }
                                                @endphp

                                                <div class="ftv-items-split">
                                                    <div class="ftv-items-left">
                                                        @foreach ($leftItems as $item)
                                                            @include('filament-timeline-view::tables.partials.item', [
                                                                'record' => $item['record'],
                                                                'order' => $item['order'],
                                                                'columnsLayout' => $columnsLayout,
                                                            ])
                                                        @endforeach
                                                    </div>
                                                    <div class="ftv-items-right">
                                                        @foreach ($rightItems as $item)
                                                            @include('filament-timeline-view::tables.partials.item', [
                                                                'record' => $item['record'],
                                                                'order' => $item['order'],
                                                                'columnsLayout' => $columnsLayout,
                                                            ])
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @else
                                                <div class="ftv-items">
                                                    @foreach ($bucketRecords as $bucketRecord)
                                                        @include('filament-timeline-view::tables.partials.item', [
                                                            'record' => $bucketRecord,
                                                            'order' => null,
                                                            'columnsLayout' => $columnsLayout,
                                                        ])
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>

                                        @if ($isGroupCollapsible)
                                            <div x-show="collapsed" x-cloak class="ftv-collapsed">
                                                {{ trans_choice('filament-timeline-view::timeline.collapsed_summary', $bucketCount, ['count' => $bucketCount]) }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        @if ($hasMorePages)
            @php
                $loadMoreWireAction = "\$set('tableRecordsPerPage', {$loadMoreNextPerPage})";
            @endphp

            <nav class="fi-pagination ftv-pagination-load-more" aria-label="{{ __('filament-timeline-view::timeline.load_more') }}">
                <x-filament::button
                    color="gray"
                    :loading-indicator="false"
                    wire:click="{{ $loadMoreWireAction }}"
                    wire:target="{{ $loadMoreWireAction }}"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="{{ $loadMoreWireAction }}">
                        {{ __('filament-timeline-view::timeline.load_more') }}
                    </span>
                    <span wire:loading wire:target="{{ $loadMoreWireAction }}" class="ftv-load-more-loading">
                        {{ \Filament\Support\generate_loading_indicator_html() }}
                        {{ __('filament-timeline-view::timeline.load_more') }}
                    </span>
                </x-filament::button>
            </nav>
        @endif

        @if ($hasFiltersBelowContent)
            <x-filament-tables::filters
                :apply-action="$filtersApplyAction"
                :form="$filtersForm"
                :heading-tag="$secondLevelHeadingTag"
                class="fi-ta-filters-below-content"
                :reset-action-position="$filtersResetActionPosition"
            />
        @endif
    </div>

    @if ($hasFiltersAfterContent)
        @include('filament-timeline-view::tables.partials.filters-sidebar', ['position' => 'after'])
    @endif

    <x-filament-actions::modals />
</div>
