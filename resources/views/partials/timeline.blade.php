@php
    $collapsedState = $timelineComponent->getTimelineCollapsedState();
    $isCollapsible = $timelineComponent->isTimelineCollapsible();
@endphp

<div
    class="ftv-shell"
    x-data="{ collapsed: {{ \Illuminate\Support\Js::from($collapsedState) }} }"
>
    @if ($groups === [])
        <x-filament::empty-state
            :heading="$emptyHeading"
            :description="$emptyDescription"
        />
    @else
        <div class="ftv-root">
            @foreach ($groups as $group)
                <div @class([
                    'ftv-group',
                    'ftv-group-last' => $loop->last,
                    'ftv-group-collapsible' => $isCollapsible,
                ])
                    @if ($isCollapsible)
                        x-bind:class="{ 'ftv-group-collapsed': collapsed['{{ $group['date_key'] }}'] ?? false }"
                    @endif
                >
                    <div class="ftv-date-row">
                        <div class="ftv-date-button">
                            <span class="ftv-date-primary text-sm font-bold text-gray-950 dark:text-white">{{ $group['display']['primary'] }}</span>

                            @if ($group['display']['secondary'])
                                <span class="ftv-date-secondary text-sm text-gray-500 dark:text-gray-400">{{ $group['display']['secondary'] }}</span>
                            @endif
                        </div>

                        @if ($isCollapsible)
                            <button
                                type="button"
                                x-on:click="collapsed['{{ $group['date_key'] }}'] = ! (collapsed['{{ $group['date_key'] }}'] ?? false)"
                                class="ftv-date-toggle"
                                x-bind:aria-expanded="(! (collapsed['{{ $group['date_key'] }}'] ?? false)).toString()"
                                aria-label="Toggle {{ $group['display']['primary'] }} timeline items"
                            >
                                <x-filament::icon
                                    alias="timeline::collapse"
                                    icon="heroicon-m-chevron-down"
                                    class="ftv-date-toggle-icon h-5 w-5 shrink-0 text-gray-400 dark:text-gray-500"
                                />
                            </button>
                        @endif
                    </div>

                    <div class="ftv-group-body @if (count($group['items'])) ftv-group-body-has-line @endif">
                        <div x-show="{{ $isCollapsible ? "(collapsed['{$group['date_key']}'] ?? false)" : 'false' }}" x-cloak>
                            <div
                                type="button"
                                class="ftv-collapsed rounded-full border border-dashed border-gray-300 bg-white text-gray-600 dark:border-white/10 dark:bg-white/5 dark:text-gray-200"
                            >
                                {{ $group['collapsed_summary'] }}
                            </div>
                        </div>

                        <div x-show="{{ $isCollapsible ? "! (collapsed['{$group['date_key']}'] ?? false)" : 'true' }}" x-cloak>
                            <div class="ftv-items">
                                @foreach ($group['items'] as $item)
                                    @php
                                        $itemTags = collect($item['tags'] ?? [])
                                            ->map(function ($timelineTag): ?array {
                                                if (is_string($timelineTag)) {
                                                    return [
                                                        'label' => $timelineTag,
                                                        'url' => null,
                                                    ];
                                                }

                                                if (! is_array($timelineTag)) {
                                                    return null;
                                                }

                                                $label = data_get($timelineTag, 'label');

                                                if (! is_string($label) || $label === '') {
                                                    return null;
                                                }

                                                $url = data_get($timelineTag, 'url');

                                                return [
                                                    'label' => $label,
                                                    'url' => is_string($url) && $url !== '' ? $url : null,
                                                ];
                                            })
                                            ->filter()
                                            ->values();
                                        $itemUser = is_array($item['user'] ?? null) ? [
                                            'name' => is_string(data_get($item, 'user.name')) ? data_get($item, 'user.name') : null,
                                            'avatar_url' => is_string(data_get($item, 'user.avatar_url')) ? data_get($item, 'user.avatar_url') : null,
                                            'url' => is_string(data_get($item, 'user.url')) ? data_get($item, 'user.url') : null,
                                        ] : null;
                                        $visibleTags = $itemTags->take(filled($item['image_url']) ? 2 : 3);
                                        $hiddenTagCount = max($itemTags->count() - $visibleTags->count(), 0);
                                    @endphp

                                    <div class="ftv-item">
                                        <span class="ftv-item-dot rounded-full"></span>

                                        <span
                                            class="ftv-item-caret"
                                            aria-hidden="true"
                                        ></span>

                                        <div class="ftv-card">
                                            <div @class([
                                                'ftv-card-layout',
                                                'ftv-card-layout-has-media' => filled($item['image_url']),
                                            ])>
                                                @if (filled($item['image_url']))
                                                    <div class="ftv-card-media">
                                                        <img
                                                            src="{{ $item['image_url'] }}"
                                                            alt="{{ $item['image_alt'] ?? $item['title'] ?? 'Timeline image' }}"
                                                            class="ftv-card-image rounded-full border border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/5"
                                                        >
                                                    </div>
                                                @endif

                                                <div class="ftv-card-copy">
                                                    @if (filled($item['title']))
                                                        <h3 class="ftv-card-title text-base font-bold text-gray-950 dark:text-white">
                                                            @if (filled($item['url']))
                                                                <a
                                                                    href="{{ $item['url'] }}"
                                                                    class="ftv-item-link"
                                                                >
                                                                    {{ $item['title'] }}
                                                                </a>
                                                            @else
                                                                {{ $item['title'] }}
                                                            @endif
                                                        </h3>
                                                    @endif

                                                    @if (filled($item['content']))
                                                        <p class="ftv-card-content text-sm leading-7 text-gray-950 dark:text-white">{{ $item['content'] }}</p>
                                                    @endif

                                                    @if (($itemTags->isNotEmpty()) || filled($item['time_label']) || filled($itemUser) || filled($item['meta']))
                                                        <div class="ftv-card-meta mt-3 text-xs font-medium text-gray-500 dark:text-gray-400">
                                                            <div class="ftv-card-meta-left">
                                                                @foreach ($visibleTags as $tag)
                                                                    @if (filled($tag['url']))
                                                                        <a
                                                                            href="{{ $tag['url'] }}"
                                                                            class="inline-flex shrink-0 items-center rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-700 dark:bg-white/10 dark:text-gray-200"
                                                                        >
                                                                            {{ $tag['label'] }}
                                                                        </a>
                                                                    @else
                                                                        <span class="inline-flex shrink-0 items-center rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-700 dark:bg-white/10 dark:text-gray-200">
                                                                            {{ $tag['label'] }}
                                                                        </span>
                                                                    @endif
                                                                @endforeach

                                                                @if ($hiddenTagCount > 0)
                                                                    <span class="inline-flex shrink-0 items-center rounded-full bg-gray-100 px-2.5 py-1 text-[11px] font-medium text-gray-700 dark:bg-white/10 dark:text-gray-200">
                                                                        +{{ $hiddenTagCount }}
                                                                    </span>
                                                                @endif

                                                                @if (($itemTags->isEmpty()) && filled($item['meta']))
                                                                    <p class="text-xs font-semibold tracking-[0.02em] text-gray-500 dark:text-gray-400">{{ $item['meta'] }}</p>
                                                                @endif
                                                            </div>

                                                            @if (filled($itemUser) || filled($item['time_label']))
                                                                <div class="ftv-card-meta-right">
                                                                    @if (filled($itemUser))
                                                                        <div class="ftv-card-meta-user">
                                                                            @if (filled($itemUser['avatar_url']))
                                                                                <img
                                                                                    src="{{ $itemUser['avatar_url'] }}"
                                                                                    alt="{{ $itemUser['name'] }}"
                                                                                    class="h-5 w-5 rounded-full border border-gray-200 bg-gray-50 object-cover dark:border-white/10 dark:bg-white/5"
                                                                                >
                                                                            @endif

                                                                            @if (filled($itemUser['url']))
                                                                                <a
                                                                                    href="{{ $itemUser['url'] }}"
                                                                                    class="ftv-card-meta-user-name font-medium text-gray-600 hover:text-gray-900 dark:text-gray-300 dark:hover:text-white"
                                                                                    title="{{ $itemUser['name'] }}"
                                                                                >
                                                                                    {{ $itemUser['name'] }}
                                                                                </a>
                                                                            @else
                                                                                <span class="ftv-card-meta-user-name font-medium text-gray-600 dark:text-gray-300" title="{{ $itemUser['name'] }}">{{ $itemUser['name'] }}</span>
                                                                            @endif
                                                                        </div>
                                                                    @endif

                                                                    @if (filled($item['time_label']))
                                                                        <span class="ftv-card-meta-time">{{ $item['time_label'] }}</span>
                                                                    @endif
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @if ($timelineComponent->hasTimelineLoadMore())
                <div class="ftv-load-more">
                    <x-filament::button
                        type="button"
                        color="gray"
                        wire:click="{{ $timelineComponent->getTimelineLoadMoreAction() }}"
                    >
                        {{ $timelineComponent->getTimelineLoadMoreLabel() }}
                    </x-filament::button>
                </div>
            @endif
        </div>
    @endif
</div>
