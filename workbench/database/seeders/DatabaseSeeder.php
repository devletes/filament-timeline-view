<?php

namespace Workbench\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Workbench\App\Models\Pulse;
use Workbench\App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Salman Hijazi',
                'password' => Hash::make('password'),
            ],
        );

        Pulse::query()->delete();

        $today = Carbon::today();

        $samples = [
            [
                'published_at' => $today->copy()->setTime(9, 12),
                'title' => 'Quarterly review next Friday',
                'body' => 'Please block your calendars for the quarterly review.',
                'category' => 'Leadership',
            ],
            [
                'published_at' => $today->copy()->setTime(14, 5),
                'title' => "It's a new day!",
                'body' => "Today I don't feel like doing anything",
                'category' => 'Article',
            ],
            [
                'published_at' => $today->copy()->subDay()->setTime(18, 23),
                'title' => 'Your privacy is important',
                'body' => 'A timely reminder about safe day-to-day information handling.',
                'category' => 'Document',
            ],
            [
                'published_at' => $today->copy()->subDay()->setTime(10, 23),
                'title' => 'Welcome to the team, PewDiePie',
                'body' => 'A quick introduction and welcome for Felix.',
                'category' => 'New Joiner',
                'hero_image_url' => 'https://i.pravatar.cc/160?img=60',
            ],
            [
                'published_at' => $today->copy()->subDays(3)->setTime(9, 0),
                'title' => 'Office closed for spring cleaning',
                'body' => 'The office will be closed for the day while we tidy up.',
                'category' => 'Announcement',
            ],
            [
                'published_at' => $today->copy()->subDays(3)->setTime(15, 30),
                'title' => 'New coffee machine in the kitchen',
                'body' => 'Stop by the kitchen to try out our new espresso setup.',
                'category' => 'Update',
            ],
            [
                'published_at' => $today->copy()->subDays(7)->setTime(11, 15),
                'title' => 'Friday town hall recap',
                'body' => 'Notes from yesterday\'s town hall are now up on the wiki.',
                'category' => 'Meeting',
            ],
        ];

        foreach ($samples as $row) {
            Pulse::query()->create([
                'author_id' => $user->id,
                'title' => $row['title'],
                'body' => $row['body'],
                'category' => $row['category'] ?? null,
                'hero_image_url' => $row['hero_image_url'] ?? null,
                'published_at' => $row['published_at'],
            ]);
        }
    }
}
