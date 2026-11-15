<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Page;
use App\Models\Project;
use Illuminate\Http\Response as HttpResponse;

class SitemapController extends Controller
{
    public function __invoke(): HttpResponse
    {
        $urls = collect([route('home'), route('about'), route('projects.index'), route('services.index'), route('blog.index'), route('contact.index')])
            ->merge(Blog::published()->whereDoesntHave('seo', fn ($query) => $query->where('noindex', true))->pluck('slug')->map(fn (string $slug): string => route('blog.show', $slug)))
            ->merge(Project::published()->whereDoesntHave('seo', fn ($query) => $query->where('noindex', true))->pluck('slug')->map(fn (string $slug): string => route('projects.show', $slug)))
            ->merge(Page::published()->whereDoesntHave('seo', fn ($query) => $query->where('noindex', true))->pluck('slug')->map(fn (string $slug): string => route('pages.show', $slug)));
        $items = $urls->map(fn (string $url): string => '<url><loc>'.e($url).'</loc></url>')->implode('');

        return response('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'.$items.'</urlset>', 200, ['Content-Type' => 'application/xml']);
    }
}
