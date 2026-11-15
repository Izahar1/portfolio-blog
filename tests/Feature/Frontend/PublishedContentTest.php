<?php

use App\Models\Blog;
use App\Models\User;

it('hides drafts even when a visitor knows their slug', function () {
    $author = User::factory()->create();
    $blog = Blog::query()->create([
        'user_id' => $author->id,
        'title' => 'Private draft',
        'slug' => 'private-draft',
        'content' => 'Draft contents',
        'status' => 'draft',
    ]);

    $this->get(route('blog.show', $blog->slug))->assertNotFound();
});

it('shows published posts on the public blog', function () {
    $author = User::factory()->create();
    $blog = Blog::query()->create([
        'user_id' => $author->id,
        'title' => 'Published post',
        'slug' => 'published-post',
        'content' => 'Public content',
        'status' => 'published',
        'published_at' => now()->subMinute(),
    ]);

    $this->get(route('blog.show', $blog->slug))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('Frontend/Blog/Show')->where('post.id', $blog->id));
});
