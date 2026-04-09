@php
    $timelineComponent = $this;
    $groups = $this->getPreparedTimelineGroups();
    $emptyHeading = $this->getTimelineEmptyStateHeading();
    $emptyDescription = $this->getTimelineEmptyStateDescription();
@endphp

@include('filament-timeline-view::partials.timeline')
