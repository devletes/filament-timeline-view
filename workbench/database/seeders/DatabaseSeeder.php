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
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $base = Carbon::parse('2026-04-08 10:57:00');

        $samples = [
            [
                'created_at' => $base->copy(),
                'title' => "It's a new day!",
                'body' => "Today I don't feel like doing anything",
                'category' => 'Article',
            ],
            [
                'created_at' => $base->copy()->subDays(8)->setTime(18, 23),
                'title' => 'Your Privacy is important',
                'body' => 'A timely reminder about safe day-to-day information handling.',
                'category' => 'Document',
            ],
            [
                'created_at' => $base->copy()->subDays(8)->setTime(10, 23),
                'title' => 'Welcome to the Team, PewDiePie',
                'body' => 'A quick introduction and welcome for Felix.',
                'category' => 'New Joiner',
                'hero_image_url' => 'https://i.pravatar.cc/160?img=60',
            ],
            [
                'created_at' => $base->copy()->subDays(14)->setTime(9, 0),
                'title' => 'Office closed for spring cleaning',
                'body' => 'The office will be closed for the day while we tidy up.',
                'category' => 'Announcement',
            ],
            [
                'created_at' => $base->copy()->subDays(15)->setTime(15, 30),
                'title' => 'New coffee machine in the kitchen',
                'body' => 'Stop by the kitchen to try out our new espresso setup.',
                'category' => 'Update',
            ],
            [
                'created_at' => $base->copy()->subDays(19)->setTime(11, 15),
                'title' => 'Quarterly review next Friday',
                'body' => 'Please block your calendars for the quarterly review.',
                'category' => 'Leadership',
            ],
        ];

        foreach ($samples as $row) {
            Pulse::query()->create([
                'author_id' => $user->id,
                'title' => $row['title'],
                'body' => $row['body'],
                'category' => $row['category'] ?? null,
                'hero_image_url' => $row['hero_image_url'] ?? null,
                'published_at' => $row['created_at'],
            ]);
        }
    }
}
