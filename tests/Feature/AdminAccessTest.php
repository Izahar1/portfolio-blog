<?php

use App\Models\User;

it('redirects visitors away from the admin dashboard', function () {
    $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
});

it('denies inactive users access to the admin dashboard', function () {
    $user = User::factory()->create()->forceFill(['is_active' => false]);

    $this->actingAs($user)->get(route('admin.dashboard'))->assertForbidden();
});

it('allows active editors to access content management', function () {
    $user = User::factory()->create()->forceFill(['role' => 'editor', 'is_active' => true]);

    $this->actingAs($user)->get(route('admin.blogs.index'))->assertOk();
});

it('forbids editors from viewing private contact inquiries', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get(route('admin.inquiries.index'))->assertForbidden();
});

it('forbids editors from publishing new blog posts', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post(route('admin.blogs.store'), [
        'title' => 'Needs approval',
        'content' => 'This post should remain a draft.',
        'status' => 'published',
    ])->assertForbidden();

    $this->assertDatabaseCount('blogs', 0);
});
