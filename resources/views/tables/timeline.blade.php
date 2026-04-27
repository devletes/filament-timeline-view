@php
    use Carbon\CarbonInterface;
    use Illuminate\Support\Carbon;

    $records = $isLoaded ? $getRecords() : null;
    $columnsLayout = $getColumnsLayout();
    $group = $getGrouping();
    $heading = $getHeading();
    $description = $getDescription();
    $hasHeader = filled($heading) || filled($description);
    $hasEmptyState = ($records !== null) && (! count($records));
    $hasPagination = ($records instanceof \Illuminate\Contracts\Pagination\Paginator)
        || ($records instanceof \Illuminate\Contracts\Pagination\CursorPaginator);
    $isDouble = ($timelineLayout ?? 'single') === 'double';

    $loadMoreStep = (int) (collect($getPaginationPageOptions())->first(fn ($option) => is_int($option)) ?? 5);
    $currentPerPage = $records instanceof \Illuminate\Contracts\Pagination\Paginator ? (int) $records->perPage() : null;
    $loadMoreNextPerPage = ($currentPerPage !== null) ? ($currentPerPage + $loadMoreStep) : null;
    $hasMorePages = $hasPagination && method_exists($records, 'hasMorePages') && $records->hasMorePages();
    $hasFooter = $hasMorePages;

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
@endphp

<div @class([
        'fi-ta-ctn',
        'fi-ta-ctn-with-header' => $hasHeader,
        'fi-ta-ctn-with-footer' => $hasFooter,
    ])>
    <div class="fi-ta-main">
        @if ($hasHeader)
            <header class="fi-ta-header-ctn">
                <div class="fi-ta-header">
                    <div class="fi-ta-header-text-ctn">
                        @if (filled($heading))
                            <h3 class="fi-ta-header-heading">{{ $heading }}</h3>
                        @endif
                        @if (filled($description))
                            <p class="fi-ta-header-description">{{ $description }}</p>
                        @endif
                    </div>
                </div>
            </header>
        @endif

        <div class="fi-ta-content-ctn">
            <div @class([
                    'fi-ta-timeline',
                    'fi-ta-timeline-double' => $isDouble,
                ])
                wire:key="{{ $this->getId() }}.table.timeline"
            >
                @if ($hasEmptyState)
                    @php
                        $emptyHeading = $getEmptyStateHeading() ?: __('filament-timeline-view::timeline.empty_state.heading');
                        $emptyDescription = $getEmptyStateDescription() ?: __('filament-timeline-view::timeline.empty_state.description');
                    @endphp

                    <div class="ftv-empty-state">
                        <h4 class="ftv-empty-state-heading">
                            {{ $emptyHeading }}
                        </h4>
                        @if (filled($emptyDescription))
                            <p class="ftv-empty-state-description">
                                {{ $emptyDescription }}
                            </p>
                        @endif
                    </div>
                @elseif ($records !== null)
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
                @endif
            </div>
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
    </div>
</div>
