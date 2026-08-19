@php
    use Filament\Actions\Action;
    use Filament\Actions\ActionGroup;
    use Filament\Actions\BulkAction;
    use Filament\Actions\View\ActionsIconAlias;
    use Filament\Support\Facades\FilamentIcon;
    use Filament\Support\Icons\Heroicon;

    $recordKey = $getRecordKey($record);
    $recordUrl = $getRecordUrl($record);
    $recordAction = $getRecordAction($record);
    $viewIcon = FilamentIcon::resolve(ActionsIconAlias::VIEW_ACTION) ?? Heroicon::Eye;
    $itemStyle = $order !== null ? 'style="--ftv-order: '.((int) $order).';"' : '';

    $rawRecordActions = $getRecordActions();
    $cardActionGroup = null;
    $cardActions = [];

    if (count($rawRecordActions) === 1 && $rawRecordActions[0] instanceof ActionGroup) {
        $group = $rawRecordActions[0]->getClone();
        $group->record($record);

        if (! $group->isHidden()) {
            $cardActionGroup = $group;
            $cardActions = array_filter(
                $group->getFlatActions(),
                fn (Action $action): bool => ! $action->isHidden(),
            );
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
            $cardActions = $flatActions;
        }
    }

    $hasActions = $cardActionGroup !== null;

    // Resource pages derive the record URL/action from the table's own view action, which the card already renders.
    $hasViewLink = (filled($recordUrl) || filled($recordAction)) && ! collect($cardActions)->contains(
        fn (Action $action): bool => (filled($recordAction) && ($action->getName() === $recordAction))
            || (filled($recordUrl) && ($action->getUrl() === $recordUrl)),
    );
@endphp

<div class="ftv-item" {!! $itemStyle !!} wire:key="{{ $this->getId() }}.table.timeline.item.{{ $recordKey }}">
    <span class="ftv-item-dot"></span>
    <span class="ftv-item-caret"></span>
    <div @class([
            'ftv-card',
            'ftv-card-with-actions' => $hasActions || $hasViewLink,
        ])>
        @foreach ($columnsLayout as $columnsLayoutComponent)
            {{ $columnsLayoutComponent->record($record)->recordKey($recordKey)->renderInLayout() }}
        @endforeach

        @if ($hasActions || $hasViewLink)
            <div class="ftv-card-actions">
                @if ($hasViewLink)
                    @if (filled($recordUrl))
                        <x-filament::link
                            class="ftv-card-view-link"
                            :href="$recordUrl"
                            :target="$shouldOpenRecordUrlInNewTab($record) ? '_blank' : null"
                            :icon="$viewIcon"
                            size="sm"
                        >
                            {{ __('filament-actions::view.single.label') }}
                        </x-filament::link>
                    @else
                        <x-filament::link
                            class="ftv-card-view-link"
                            tag="button"
                            :icon="$viewIcon"
                            size="sm"
                            wire:click="mountTableAction('{{ $recordAction }}', '{{ $recordKey }}')"
                        >
                            {{ __('filament-actions::view.single.label') }}
                        </x-filament::link>
                    @endif
                @endif

                @if ($hasActions)
                    {{ $cardActionGroup }}
                @endif
            </div>
        @endif
    </div>
</div>
