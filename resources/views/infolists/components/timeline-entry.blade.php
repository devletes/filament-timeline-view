@php
    $groups = $getPreparedTimelineGroups();
    $emptyHeading = $getTimelineEmptyStateHeading();
    $emptyDescription = $getTimelineEmptyStateDescription();
    $timelineComponent = $entry;
@endphp

@include('filament-timeline-view::partials.timeline')
