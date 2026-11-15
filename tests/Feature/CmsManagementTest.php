<?php

use App\Models\Blog;
use App\Models\Category;
use App\Models\Page;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Skill;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;

it('creates a post and syncs its category and tags in the admin cms', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();
    $category = Category::query()->create(['name' => 'Engineering', 'slug' => 'engineering']);
    $tag = Tag::query()->create(['name' => 'Laravel', 'slug' => 'laravel']);

    $this->actingAs($admin)->post(route('admin.blogs.store'), [
        'title' => 'A CMS post',
        'excerpt' => 'A short introduction.',
        'content' => 'The full post content.',
        'category_id' => $category->id,
        'tag_ids' => [$tag->id],
        'status' => 'published',
        'featured' => true,
    ])->assertRedirect(route('admin.blogs.index'));

    $blog = Blog::query()->where('slug', 'a-cms-post')->firstOrFail();
    $this->assertDatabaseHas('blog_tag', ['blog_id' => $blog->id, 'tag_id' => $tag->id]);
    expect($blog->status)->toBe('published');
});

it('publishes scheduled posts once their publication time arrives', function () {
    $author = User::factory()->create();
    $blog = Blog::query()->create([
        'user_id' => $author->id,
        'title' => 'Scheduled post',
        'slug' => 'scheduled-post',
        'content' => 'This post is ready.',
        'status' => 'scheduled',
        'published_at' => now()->subMinute(),
    ]);

    Artisan::call('app:publish-scheduled-blogs');

    expect($blog->fresh()->status)->toBe('published');
});

it('creates working permanent redirects when a published blog slug changes', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();
    $blog = Blog::query()->create([
        'user_id' => $admin->id,
        'title' => 'Old title',
        'slug' => 'old-title',
        'content' => 'Post body.',
        'status' => 'published',
        'published_at' => now()->subDay(),
    ]);

    $this->actingAs($admin)->put(route('admin.blogs.update', $blog), [
        'title' => 'New title',
        'slug' => 'new-title',
        'content' => 'Post body.',
        'status' => 'published',
        'published_at' => now()->subDay()->toDateTimeString(),
        'tag_ids' => [],
        'featured' => false,
    ])->assertRedirect(route('admin.blogs.index'));

    expect(Redirect::query()->where('old_path', '/blog/old-title')->value('new_path'))->toBe('/blog/new-title');
    $this->get('/blog/old-title')->assertRedirect('/blog/new-title');
});

it('stores project categories and skills and filters public projects by category', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();
    $category = ProjectCategory::query()->create(['name' => 'Web apps', 'slug' => 'web-apps']);
    $skill = Skill::query()->create(['name' => 'Vue', 'slug' => 'vue', 'is_visible' => true]);

    $this->actingAs($admin)->post(route('admin.projects.store'), [
        'title' => 'Portfolio app',
        'slug' => '',
        'summary' => 'A project summary.',
        'description' => 'Project details.',
        'status' => 'published',
        'featured' => false,
        'sort_order' => 1,
        'category_ids' => [$category->id],
        'skill_ids' => [$skill->id],
        'gallery_media_ids' => [],
    ])->assertRedirect(route('admin.projects.index'));

    $project = Project::query()->where('slug', 'portfolio-app')->firstOrFail();
    $this->assertDatabaseHas('project_project_category', ['project_id' => $project->id, 'project_category_id' => $category->id]);
    $this->assertDatabaseHas('project_skill', ['project_id' => $project->id, 'skill_id' => $skill->id]);
    $this->get(route('projects.index', ['category' => 'web-apps']))->assertInertia(fn ($page) => $page->has('projects.data', 1)->where('projects.data.0.slug', 'portfolio-app'));
});

it('creates a custom page with validated ordered sections', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();

    $this->actingAs($admin)->post(route('admin.pages.store'), [
        'title' => 'Services overview',
        'slug' => '',
        'status' => 'published',
        'template' => 'default',
        'sections' => [
            ['type' => 'hero', 'heading' => 'Welcome', 'content_json' => ['title' => 'Services', 'subtitle' => 'What I do'], 'is_enabled' => true],
            ['type' => 'rich_text', 'heading' => 'Details', 'content_json' => ['content' => 'More details.'], 'is_enabled' => false],
        ],
    ])->assertRedirect(route('admin.pages.index'));

    $page = Page::query()->where('slug', 'services-overview')->firstOrFail();
    expect($page->sections()->orderBy('sort_order')->pluck('type')->all())->toBe(['hero', 'rich_text']);
    expect($page->sections()->where('is_enabled', false)->count())->toBe(1);
});

it('saves site settings and shares the values with public pages', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'super_admin'])->save();

    $this->actingAs($admin)->patch(route('admin.settings.update'), [
        'site_name' => 'Casey Portfolio',
        'seo_title' => 'Casey | Developer',
        'seo_description' => 'A portfolio and journal.',
        'contact_email' => 'casey@example.test',
    ])->assertRedirect();

    expect(Setting::query()->where('setting_key', 'site_name')->firstOrFail()->value)->toBe('Casey Portfolio');
    $this->get(route('home'))->assertInertia(fn ($page) => $page->where('site.site_name', 'Casey Portfolio'));
});

it('protects the last active super admin from demotion', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'super_admin', 'is_active' => true])->save();

    $this->actingAs($admin)->patch(route('admin.users.update', $admin), [
        'role' => 'editor',
        'is_active' => true,
    ])->assertSessionHasErrors('role');

    expect($admin->fresh()->role)->toBe('super_admin');
});

it('manages portfolio services through the shared admin resource screens', function () {
    $admin = User::factory()->create();
    $admin->forceFill(['role' => 'admin'])->save();

    $this->actingAs($admin)->post(route('admin.resources.store', 'services'), [
        'title' => 'Web application development',
        'slug' => '',
        'description' => 'Building fast, accessible web products.',
        'status' => 'published',
        'sort_order' => 1,
    ])->assertRedirect(route('admin.resources.index', 'services'));

    $service = Service::query()->where('slug', 'web-application-development')->firstOrFail();
    expect($service->status)->toBe('published');
    $this->actingAs($admin)->get(route('admin.resources.index', 'services'))->assertOk();
});
