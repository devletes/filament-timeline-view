@php
    $groups = $getPreparedTimelineGroups();
    $emptyHeading = $getTimelineEmptyStateHeading();
    $emptyDescription = $getTimelineEmptyStateDescription();
    $timelineComponent = $schemaComponent;
@endphp

@include('filament-timeline-view::partials.timeline')
