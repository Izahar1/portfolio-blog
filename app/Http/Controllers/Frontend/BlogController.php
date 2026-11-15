<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Redirect;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:120']]);
        $posts = Blog::published()->with(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug', 'featuredMedia:id,disk,path,alt_text'])
            ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($nested) => $nested->where('title', 'like', "%{$search}%")->orWhere('excerpt', 'like', "%{$search}%")))
            ->latest('published_at')->paginate(9)->withQueryString();

        return Inertia::render('Frontend/Blog/Index', [
            'posts' => $posts,
            'categories' => Category::query()->withCount(['blogs' => fn ($query) => $query->published()])->orderBy('name')->get(['id', 'name', 'slug']),
            'filters' => ['search' => $filters['search'] ?? ''],
        ]);
    }

    public function category(string $slug): Response
    {
        $category = Category::query()->where('slug', $slug)->firstOrFail();
        $posts = Blog::published()->where('category_id', $category->id)->with(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug', 'featuredMedia:id,disk,path,alt_text'])->latest('published_at')->paginate(9);

        return Inertia::render('Frontend/Blog/Index', [
            'posts' => $posts,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'filters' => ['search' => '', 'category' => $category->slug],
        ]);
    }

    public function tag(string $slug): Response
    {
        $tag = Tag::query()->where('slug', $slug)->firstOrFail();
        $posts = $tag->blogs()->published()->with(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug', 'featuredMedia:id,disk,path,alt_text'])->latest('published_at')->paginate(9);

        return Inertia::render('Frontend/Blog/Index', [
            'posts' => $posts,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name', 'slug']),
            'filters' => ['search' => '', 'tag' => $tag->slug],
        ]);
    }

    public function show(string $slug): Response|RedirectResponse
    {
        $post = Blog::published()->with(['author:id,name', 'category:id,name,slug', 'tags:id,name,slug', 'featuredMedia:id,disk,path,alt_text', 'seo'])->where('slug', $slug)->first();

        if (! $post) {
            $redirect = Redirect::query()->where('old_path', '/blog/'.$slug)->first();
            abort_unless($redirect, 404);

            return redirect($redirect->new_path, $redirect->status_code);
        }

        $post->increment('view_count');

        return Inertia::render('Frontend/Blog/Show', ['post' => $post]);
    }
}
