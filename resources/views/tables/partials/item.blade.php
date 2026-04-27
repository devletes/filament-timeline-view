@php
    use Filament\Actions\ActionGroup;
    use Filament\Actions\BulkAction;

    $recordKey = $getRecordKey($record);
    $itemStyle = $order !== null ? 'style="--ftv-order: '.((int) $order).';"' : '';

    $rawRecordActions = $getRecordActions();
    $cardActionGroup = null;

    if (count($rawRecordActions) === 1 && $rawRecordActions[0] instanceof ActionGroup) {
        $group = $rawRecordActions[0]->getClone();
        $group->record($record);

        if (! $group->isHidden()) {
            $cardActionGroup = $group;
        }
    } else {
        $flatActions = [];

        foreach ($rawRecordActions as $action) {
            $clone = $action->getClone();

            if (! $clone instanceof BulkAction) {
                $clone->record($record);
            }

            if ($clone->isHidden()) {
                continue;
            }

            if ($clone instanceof ActionGroup) {
                foreach ($clone->getFlatActions() as $innerAction) {
                    $innerClone = $innerAction->getClone();

                    if (! $innerClone instanceof BulkAction) {
                        $innerClone->record($record);
                    }

                    if (! $innerClone->isHidden()) {
                        $flatActions[] = $innerClone;
                    }
                }
            } else {
                $flatActions[] = $clone;
            }
        }

        if (count($flatActions) > 0) {
            $cardActionGroup = ActionGroup::make($flatActions)->color('gray');
        }
    }

    $hasActions = $cardActionGroup !== null;
@endphp

<div class="ftv-item" {!! $itemStyle !!} wire:key="{{ $this->getId() }}.table.timeline.item.{{ $recordKey }}">
    <span class="ftv-item-dot"></span>
    <span class="ftv-item-caret"></span>
    <div @class([
            'ftv-card',
            'ftv-card-with-actions' => $hasActions,
        ])>
        @foreach ($columnsLayout as $columnsLayoutComponent)
            {{ $columnsLayoutComponent->record($record)->recordKey($recordKey)->renderInLayout() }}
        @endforeach

        @if ($hasActions)
            <div class="ftv-card-actions">
                {{ $cardActionGroup }}
            </div>
        @endif
    </div>
</div>
