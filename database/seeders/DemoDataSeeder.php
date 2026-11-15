<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\Category;
use App\Models\Comment;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Inquiry;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Profile;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Redirect;
use App\Models\SeoMeta;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Skill;
use App\Models\SocialLink;
use App\Models\Tag;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->error('Demo sample data is disabled in production.');

            return;
        }

        DB::transaction(function (): void {
            $user = User::query()->updateOrCreate(
                ['email' => 'demo@example.test'],
                [
                    'name' => 'Casey Morgan',
                    'password' => 'password',
                ],
            );
            $user->forceFill(['role' => 'super_admin', 'is_active' => true, 'email_verified_at' => now()])->save();

            $profile = Profile::query()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'full_name' => 'Casey Morgan',
                    'headline' => 'Full-stack developer building thoughtful digital products',
                    'bio' => 'I build accessible, reliable web applications with Laravel and Vue. I enjoy turning complex ideas into clear experiences and working closely with teams from first sketch to launch.',
                    'location' => 'Pune, India',
                    'availability_status' => 'Available for select projects',
                ],
            );

            $this->seedExperienceAndEducation($profile);
            $this->seedPortfolioContent($user);
            $this->seedBlogContent($user);
            $this->seedPages();
            $this->seedSiteConfiguration();
        });

        $this->command?->info('Demo content seeded. Local admin login: demo@example.test / password');
    }

    private function seedExperienceAndEducation(Profile $profile): void
    {
        Experience::query()->updateOrCreate(
            ['profile_id' => $profile->id, 'company' => 'Northstar Studio', 'job_title' => 'Senior Web Developer'],
            [
                'location' => 'Remote',
                'start_date' => '2022-03-01',
                'end_date' => null,
                'is_current' => true,
                'description' => 'Lead development of customer-facing products, maintain shared UI foundations, and mentor a small product team.',
                'sort_order' => 1,
            ],
        );

        Experience::query()->updateOrCreate(
            ['profile_id' => $profile->id, 'company' => 'Pixel Foundry', 'job_title' => 'Web Developer'],
            [
                'location' => 'Pune, India',
                'start_date' => '2019-06-01',
                'end_date' => '2022-02-28',
                'is_current' => false,
                'description' => 'Built content sites and internal tools with a focus on performance and maintainable code.',
                'sort_order' => 2,
            ],
        );

        Education::query()->updateOrCreate(
            ['profile_id' => $profile->id, 'institution' => 'University of Pune', 'qualification' => 'Bachelor of Computer Applications'],
            [
                'field_of_study' => 'Computer Science',
                'start_date' => '2015-06-01',
                'end_date' => '2018-05-31',
                'description' => 'Coursework in software engineering, databases, and web development.',
                'sort_order' => 1,
            ],
        );
    }

    private function seedPortfolioContent(User $user): void
    {
        $projectCategories = collect([
            ['name' => 'Web Applications', 'slug' => 'web-applications', 'description' => 'Product and platform work.'],
            ['name' => 'Open Source', 'slug' => 'open-source', 'description' => 'Community projects and developer tools.'],
        ])->mapWithKeys(fn (array $attributes): array => [
            $attributes['slug'] => ProjectCategory::query()->updateOrCreate(['slug' => $attributes['slug']], $attributes),
        ]);

        $skills = collect([
            ['name' => 'Laravel', 'slug' => 'laravel', 'category' => 'Backend', 'proficiency' => 90, 'sort_order' => 1],
            ['name' => 'Vue.js', 'slug' => 'vuejs', 'category' => 'Frontend', 'proficiency' => 85, 'sort_order' => 2],
            ['name' => 'Inertia.js', 'slug' => 'inertiajs', 'category' => 'Frontend', 'proficiency' => 80, 'sort_order' => 3],
            ['name' => 'MySQL', 'slug' => 'mysql', 'category' => 'Data', 'proficiency' => 75, 'sort_order' => 4],
        ])->mapWithKeys(fn (array $attributes): array => [
            $attributes['slug'] => Skill::query()->updateOrCreate(['slug' => $attributes['slug']], [...$attributes, 'is_visible' => true]),
        ]);

        $projects = [
            [
                'title' => 'Fieldnotes',
                'slug' => 'fieldnotes',
                'summary' => 'A calm, collaborative workspace for teams documenting research in the field.',
                'description' => "Fieldnotes helps distributed teams collect observations, organize evidence, and share decisions.\n\nI designed the data model, built the publishing workflow, and shipped an accessible Vue interface backed by Laravel.",
                'demo_url' => 'https://example.com/fieldnotes',
                'repository_url' => 'https://github.com/example/fieldnotes',
                'status' => 'published',
                'featured' => true,
                'sort_order' => 1,
                'published_at' => now()->subMonths(2),
                'categories' => ['web-applications'],
                'skills' => ['laravel', 'vuejs', 'inertiajs', 'mysql'],
            ],
            [
                'title' => 'Laravel Health Check',
                'slug' => 'laravel-health-check',
                'summary' => 'A small open-source dashboard for checking application dependencies and service status.',
                'description' => "A lightweight status dashboard with clear failure messages and configurable checks.\n\nThe project emphasizes simple operations, useful logs, and a small dependency footprint.",
                'demo_url' => null,
                'repository_url' => 'https://github.com/example/laravel-health-check',
                'status' => 'published',
                'featured' => true,
                'sort_order' => 2,
                'published_at' => now()->subMonth(),
                'categories' => ['open-source'],
                'skills' => ['laravel', 'mysql'],
            ],
            [
                'title' => 'Studio Booking Portal',
                'slug' => 'studio-booking-portal',
                'summary' => 'A scheduling portal for a neighborhood photography studio.',
                'description' => 'A sample project in progress with appointment management and a simple staff workflow.',
                'demo_url' => null,
                'repository_url' => null,
                'status' => 'draft',
                'featured' => false,
                'sort_order' => 3,
                'published_at' => null,
                'categories' => ['web-applications'],
                'skills' => ['laravel', 'vuejs'],
            ],
        ];

        foreach ($projects as $attributes) {
            $categorySlugs = $attributes['categories'];
            $skillSlugs = $attributes['skills'];
            unset($attributes['categories'], $attributes['skills']);

            $project = Project::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                [...$attributes, 'user_id' => $user->id],
            );
            $project->categories()->sync($projectCategories->only($categorySlugs)->pluck('id'));
            $project->skills()->sync($skills->only($skillSlugs)->pluck('id'));

            SeoMeta::query()->updateOrCreate(
                ['seoable_type' => Project::class, 'seoable_id' => $project->id],
                ['meta_title' => $project->title.' | Casey Morgan', 'meta_description' => $project->summary, 'noindex' => $project->status !== 'published'],
            );
        }

        foreach ([
            ['title' => 'Product development', 'slug' => 'product-development', 'description' => 'From discovery and architecture through implementation, testing, and launch.', 'icon' => 'layers', 'sort_order' => 1],
            ['title' => 'Laravel consulting', 'slug' => 'laravel-consulting', 'description' => 'Practical help with Laravel applications, database design, and performance.', 'icon' => 'code', 'sort_order' => 2],
            ['title' => 'Frontend engineering', 'slug' => 'frontend-engineering', 'description' => 'Responsive, accessible interfaces with Vue and modern CSS.', 'icon' => 'layout', 'sort_order' => 3],
        ] as $service) {
            Service::query()->updateOrCreate(['slug' => $service['slug']], [...$service, 'status' => 'published']);
        }

        foreach ([
            ['client_name' => 'Jordan Lee', 'client_role' => 'Product Lead', 'company' => 'Northstar Studio', 'quote' => 'Casey brought structure to a complicated project and kept the team focused on the people using it.', 'rating' => 5, 'sort_order' => 1, 'status' => 'published'],
            ['client_name' => 'Priya Shah', 'client_role' => 'Founder', 'company' => 'Fieldwork Co.', 'quote' => 'Clear communication, thoughtful technical choices, and a dependable launch.', 'rating' => 5, 'sort_order' => 2, 'status' => 'published'],
        ] as $testimonial) {
            Testimonial::query()->updateOrCreate(['client_name' => $testimonial['client_name'], 'company' => $testimonial['company']], $testimonial);
        }
    }

    private function seedBlogContent(User $user): void
    {
        $categories = collect([
            ['name' => 'Engineering', 'slug' => 'engineering', 'description' => 'Notes on building and maintaining software.'],
            ['name' => 'Workflow', 'slug' => 'workflow', 'description' => 'Habits and tools for thoughtful product work.'],
        ])->mapWithKeys(fn (array $attributes): array => [
            $attributes['slug'] => Category::query()->updateOrCreate(['slug' => $attributes['slug']], $attributes),
        ]);
        $tags = collect([
            ['name' => 'Laravel', 'slug' => 'laravel'],
            ['name' => 'Vue', 'slug' => 'vue'],
            ['name' => 'Accessibility', 'slug' => 'accessibility'],
            ['name' => 'Performance', 'slug' => 'performance'],
        ])->mapWithKeys(fn (array $attributes): array => [
            $attributes['slug'] => Tag::query()->updateOrCreate(['slug' => $attributes['slug']], $attributes),
        ]);

        $posts = [
            [
                'title' => 'A Practical Guide to Shipping a Laravel Feature',
                'slug' => 'shipping-a-laravel-feature',
                'excerpt' => 'A small, repeatable process for taking a feature from a clear requirement to a reliable release.',
                'content' => "Start with the user outcome and write down what success looks like. Keep the first version small enough to review.\n\nModel the data and authorization rules before building the interface. Add focused feature tests around the risky paths, then verify the production asset build.\n\nA feature is ready when the behavior is clear, the failure states are handled, and another developer can maintain it.",
                'category' => 'engineering',
                'tags' => ['laravel', 'performance'],
                'status' => 'published',
                'published_at' => now()->subDays(12),
                'featured' => true,
            ],
            [
                'title' => 'Accessible Interfaces Start with Good Defaults',
                'slug' => 'accessible-interfaces-good-defaults',
                'excerpt' => 'Semantic HTML, keyboard behavior, and useful labels make everyday interfaces easier for everyone.',
                'content' => "Accessibility is easier to maintain when it is part of the first implementation. Use native controls, connect labels to inputs, and keep a visible focus style.\n\nTest a page with a keyboard before adding custom interactions. Prefer clear errors beside the field that needs attention and give images meaningful alternative text.",
                'category' => 'workflow',
                'tags' => ['accessibility', 'vue'],
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'featured' => false,
            ],
            [
                'title' => 'Notes on a Faster Content Pipeline',
                'slug' => 'faster-content-pipeline',
                'excerpt' => 'Draft ideas for reducing friction between writing, review, and publishing.',
                'content' => 'This draft is included as an example of editorial content that is not visible on the public site.',
                'category' => 'workflow',
                'tags' => ['performance'],
                'status' => 'draft',
                'published_at' => null,
                'featured' => false,
            ],
            [
                'title' => 'Planning a Content Workflow That Scales',
                'slug' => 'planning-a-content-workflow',
                'excerpt' => 'A scheduled example post for trying the publishing queue.',
                'content' => 'This sample is scheduled for next week so the publishing workflow can be explored locally.',
                'category' => 'engineering',
                'tags' => ['laravel', 'vue'],
                'status' => 'scheduled',
                'published_at' => now()->addWeek(),
                'featured' => false,
            ],
        ];

        foreach ($posts as $attributes) {
            $categorySlug = $attributes['category'];
            $tagSlugs = $attributes['tags'];
            unset($attributes['category'], $attributes['tags']);

            $post = Blog::query()->updateOrCreate(
                ['slug' => $attributes['slug']],
                [...$attributes, 'user_id' => $user->id, 'category_id' => $categories[$categorySlug]->id],
            );
            $post->tags()->sync($tags->only($tagSlugs)->pluck('id'));
            SeoMeta::query()->updateOrCreate(
                ['seoable_type' => Blog::class, 'seoable_id' => $post->id],
                ['meta_title' => $post->title, 'meta_description' => $post->excerpt, 'noindex' => $post->status !== 'published'],
            );
        }

        $firstPost = Blog::query()->where('slug', 'shipping-a-laravel-feature')->firstOrFail();
        Comment::query()->updateOrCreate(
            ['blog_id' => $firstPost->id, 'email' => 'reader@example.test'],
            ['name' => 'Alex Reader', 'body' => 'The release checklist is a useful reminder. Thanks for sharing.', 'status' => 'approved', 'approved_at' => now()],
        );
    }

    private function seedPages(): void
    {
        $page = Page::query()->updateOrCreate(
            ['slug' => 'now'],
            ['title' => 'Now', 'status' => 'published', 'published_at' => now()->subDay(), 'template' => 'default'],
        );

        $page->sections()->updateOrCreate(
            ['section_key' => 'now-hero'],
            ['type' => 'hero', 'heading' => 'A little about my work', 'content_json' => ['title' => 'Building useful things for the web', 'subtitle' => 'I work with small teams to ship accessible, dependable products.', 'cta_label' => 'See my projects', 'cta_url' => '/projects'], 'sort_order' => 1, 'is_enabled' => true],
        );
        $page->sections()->updateOrCreate(
            ['section_key' => 'now-contact'],
            ['type' => 'contact_cta', 'heading' => 'Have a project in mind?', 'content_json' => ['body' => 'Tell me what you are working on and where you need a hand.', 'button_label' => 'Get in touch', 'button_url' => '/contact'], 'sort_order' => 2, 'is_enabled' => true],
        );
        SeoMeta::query()->updateOrCreate(
            ['seoable_type' => Page::class, 'seoable_id' => $page->id],
            ['meta_title' => 'Now | Casey Morgan', 'meta_description' => 'What Casey is working on and thinking about.', 'noindex' => false],
        );
    }

    private function seedSiteConfiguration(): void
    {
        foreach ([
            'site_name' => 'Casey Morgan — Developer',
            'seo_title' => 'Casey Morgan | Full-stack Developer',
            'seo_description' => 'Portfolio, selected projects, and notes on building for the web.',
            'contact_email' => 'hello@example.test',
        ] as $key => $value) {
            Setting::query()->updateOrCreate(
                ['group_name' => 'site', 'setting_key' => $key],
                ['value' => $value, 'value_type' => 'string'],
            );
        }

        $menu = Menu::query()->updateOrCreate(['location' => 'header'], ['name' => 'Main navigation']);
        foreach ([
            ['label' => 'Home', 'url' => '/', 'sort_order' => 1],
            ['label' => 'About', 'url' => '/about', 'sort_order' => 2],
            ['label' => 'Projects', 'url' => '/projects', 'sort_order' => 3],
            ['label' => 'Writing', 'url' => '/blog', 'sort_order' => 4],
            ['label' => 'Contact', 'url' => '/contact', 'sort_order' => 5],
        ] as $item) {
            MenuItem::query()->updateOrCreate(
                ['menu_id' => $menu->id, 'label' => $item['label']],
                ['link_type' => 'url', 'url' => $item['url'], 'target' => '_self', 'sort_order' => $item['sort_order'], 'is_enabled' => true],
            );
        }

        foreach ([
            ['platform' => 'GitHub', 'url' => 'https://github.com/example', 'icon' => 'github', 'sort_order' => 1],
            ['platform' => 'LinkedIn', 'url' => 'https://www.linkedin.com/in/example', 'icon' => 'linkedin', 'sort_order' => 2],
        ] as $social) {
            SocialLink::query()->updateOrCreate(['platform' => $social['platform']], [...$social, 'is_enabled' => true]);
        }

        foreach ([
            ['name' => 'Taylor Reed', 'email' => 'taylor@example.test', 'subject' => 'Portfolio collaboration', 'message' => 'I would like to discuss a small product design and development project.', 'status' => 'new'],
            ['name' => 'Morgan Ellis', 'email' => 'morgan@example.test', 'subject' => 'Laravel consulting', 'message' => 'Could you review our application performance and suggest next steps?', 'status' => 'read'],
        ] as $inquiry) {
            Inquiry::query()->updateOrCreate(
                ['email' => $inquiry['email'], 'subject' => $inquiry['subject']],
                $inquiry,
            );
        }

        Redirect::query()->updateOrCreate(
            ['old_path' => '/my-old-portfolio'],
            ['new_path' => '/projects', 'status_code' => 301],
        );
    }
}
