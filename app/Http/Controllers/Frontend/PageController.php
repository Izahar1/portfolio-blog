<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Media;
use App\Models\Page;
use App\Models\Project;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\Skill;
use App\Models\Testimonial;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    public function show(string $slug): Response|RedirectResponse
    {
        $page = Page::published()->with(['seo', 'sections' => fn ($query) => $query->where('is_enabled', true)])->where('slug', $slug)->first();

        if (! $page) {
            $redirect = Redirect::query()->where('old_path', '/pages/'.$slug)->first();
            abort_unless($redirect, 404);

            return redirect($redirect->new_path, $redirect->status_code);
        }

        $sections = $page->sections->map(function ($section): array {
            $content = $section->content_json ?? [];
            $content['items'] = match ($section->type) {
                'skills' => Skill::query()->where('is_visible', true)->when($content['selected_skill_ids'] ?? null, fn ($query, $ids) => $query->whereIn('id', $ids))->orderBy('sort_order')->get(['id', 'name', 'category']),
                'featured_projects' => Project::published()->when($content['selected_project_ids'] ?? null, fn ($query, $ids) => $query->whereIn('id', $ids))->orderBy('sort_order')->limit($content['limit'] ?? 3)->get(['id', 'title', 'slug', 'summary']),
                'services' => Service::query()->where('status', 'published')->when($content['selected_service_ids'] ?? null, fn ($query, $ids) => $query->whereIn('id', $ids))->orderBy('sort_order')->get(['id', 'title', 'slug', 'description']),
                'testimonials' => Testimonial::query()->where('status', 'published')->when($content['selected_testimonial_ids'] ?? null, fn ($query, $ids) => $query->whereIn('id', $ids))->orderBy('sort_order')->get(['id', 'client_name', 'client_role', 'company', 'quote']),
                'latest_blogs' => Blog::published()->with('category:id,name')->latest('published_at')->limit($content['limit'] ?? 3)->get(['id', 'title', 'slug', 'excerpt', 'category_id']),
                default => collect(),
            };

            if (isset($content['background_media_id'])) {
                $content['background_image'] = Media::query()->find($content['background_media_id'])?->url;
            }

            if (isset($content['image_media_id'])) {
                $content['image'] = Media::query()->find($content['image_media_id'])?->url;
            }

            return [
                'id' => $section->id,
                'type' => $section->type,
                'heading' => $section->heading,
                'content_json' => $content,
            ];
        });

        return Inertia::render('Frontend/Pages/Show', [
            'page' => [...$page->only(['id', 'title', 'slug', 'template']), 'sections' => $sections, 'seo' => $page->seo],
        ]);
    }
}
