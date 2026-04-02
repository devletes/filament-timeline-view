<?php

use Devletes\FilamentTimelineView\Tests\Fixtures\TestTimelineWidget;
use Livewire\Livewire;

beforeEach(function (): void {
    TestTimelineWidget::$items = [];
    TestTimelineWidget::$groups = [];
    TestTimelineWidget::$hasMore = false;
    TestTimelineWidget::$loadMoreCalls = 0;
});

it('renders grouped dates from flat items', function (): void {
    TestTimelineWidget::$items = [
        [
            'id' => 2,
            'date_key' => '2026-04-02',
            'date_label' => 'Thursday',
            'title' => 'Second',
            'content' => 'Second body',
        ],
        [
            'id' => 1,
            'date_key' => '2026-04-01',
            'date_label' => 'Wednesday',
            'title' => 'First',
            'content' => 'First body',
        ],
    ];

    Livewire::test(TestTimelineWidget::class)
        ->assertSee('Thursday')
        ->assertSee('Wednesday')
        ->assertSee('Second body')
        ->assertSee('First body');
});

it('shows collapsed group counts from provided grouped input', function (): void {
    TestTimelineWidget::$groups = [
        [
            'date_key' => '2026-04-01',
            'date_label' => 'Wednesday',
            'collapsed' => true,
            'items' => [
                ['id' => 1, 'title' => 'Post A', 'content' => 'Body A'],
                ['id' => 2, 'title' => 'Post B', 'content' => 'Body B'],
                ['id' => 3, 'title' => 'Post C', 'content' => 'Body C'],
            ],
        ],
    ];

    Livewire::test(TestTimelineWidget::class)
        ->assertSee('3 posts hidden');
});

it('triggers the generic load more interaction when requested', function (): void {
    TestTimelineWidget::$hasMore = true;

    Livewire::test(TestTimelineWidget::class)
        ->call('loadMore')
        ->assertSet('visibleItemCount', 20);

    expect(TestTimelineWidget::$loadMoreCalls)->toBe(1);
});

it('renders an optional item image when provided', function (): void {
    TestTimelineWidget::$items = [
        [
            'id' => 1,
            'date_key' => '2026-04-01',
            'date_label' => 'Wednesday',
            'title' => 'Image post',
            'content' => 'Body with image',
            'image_url' => 'https://example.com/post-image.jpg',
            'image_alt' => 'Post image alt',
        ],
    ];

    Livewire::test(TestTimelineWidget::class)
        ->assertSee('https://example.com/post-image.jpg')
        ->assertSee('Post image alt');
});

it('renders tags and user meta when provided', function (): void {
    TestTimelineWidget::$items = [
        [
            'id' => 1,
            'date_key' => '2026-04-01',
            'date_label' => 'Wednesday',
            'title' => 'Tagged post',
            'content' => 'Body',
            'tags' => ['Document'],
            'user' => [
                'name' => 'Sara Khan',
                'avatar_url' => 'https://example.com/avatar.jpg',
                'url' => 'https://example.com/users/sara',
            ],
            'time_label' => '9:00 AM',
        ],
    ];

    Livewire::test(TestTimelineWidget::class)
        ->assertSee('Document')
        ->assertSee('Sara Khan')
        ->assertSee('9:00 AM')
        ->assertSee('https://example.com/users/sara');
});

it('renders very long content without breaking the timeline item contract', function (): void {
    TestTimelineWidget::$items = [
        [
            'id' => 1,
            'date_key' => '2026-04-01',
            'date_label' => 'Wednesday',
            'title' => 'This is an intentionally very long timeline title meant to simulate an unusually verbose announcement headline that keeps going well beyond a normal card heading length',
            'content' => 'This is an intentionally long description designed to simulate a post body that wraps across multiple lines and keeps going so we can exercise the timeline layout under heavier real-world content density without changing the rendering contract.',
            'tags' => [
                'Document',
                'Compliance Update',
                'Operations',
                'Leadership Announcement',
                'Quarterly Review',
                'Internal Memo',
            ],
            'user' => [
                'name' => 'Alexandria Catherine Montgomery-Smythe',
                'avatar_url' => 'https://example.com/very-long-avatar.jpg',
                'url' => 'https://example.com/users/alexandria-catherine-montgomery-smythe',
            ],
            'time_label' => '11:59 PM',
        ],
    ];

    Livewire::test(TestTimelineWidget::class)
        ->assertSee('intentionally very long timeline title', escape: false)
        ->assertSee('intentionally long description', escape: false)
        ->assertSee('Document')
        ->assertSee('Compliance Update')
        ->assertSee('Operations')
        ->assertSee('+3')
        ->assertDontSee('Leadership Announcement')
        ->assertSee('Alexandria Catherine Montgomery-Smythe')
        ->assertSee('11:59 PM');
});
