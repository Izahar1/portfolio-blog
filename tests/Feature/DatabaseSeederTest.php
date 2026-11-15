<?php

use App\Models\Blog;
use App\Models\Page;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\DemoDataSeeder;

it('seeds reusable demo content without creating duplicates', function () {
    $this->seed(DemoDataSeeder::class);
    $counts = [
        User::query()->count(),
        Blog::query()->count(),
        Project::query()->count(),
        Page::query()->count(),
    ];

    $this->seed(DemoDataSeeder::class);

    expect([User::query()->count(), Blog::query()->count(), Project::query()->count(), Page::query()->count()])->toBe($counts);
    expect(User::query()->where('email', 'demo@example.test')->value('role'))->toBe('super_admin');
    expect(Blog::published()->count())->toBe(2);
    expect(Project::published()->count())->toBe(2);
});
