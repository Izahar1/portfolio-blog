<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BlogRequest;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Media;
use App\Models\Redirect;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Blogs/Index', [
            'blogs' => Blog::query()->with('category:id,name')->withCount('tags')->latest()->paginate(15),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Blogs/Form', ['blog' => null, ...$this->formOptions()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BlogRequest $request): RedirectResponse
    {
        $data = $this->prepareData($request);
        DB::transaction(function () use ($request, $data): void {
            $blog = Blog::query()->create([...$data, 'user_id' => $request->user()->id]);
            $blog->tags()->sync($request->validated('tag_ids', []));
        });

        return to_route('admin.blogs.index')->with('message', 'Blog post created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Blog $blog): RedirectResponse
    {
        return to_route('admin.blogs.edit', $blog);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Blog $blog): Response
    {
        return Inertia::render('Admin/Blogs/Form', ['blog' => $blog->load('tags:id'), ...$this->formOptions()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BlogRequest $request, Blog $blog): RedirectResponse
    {
        DB::transaction(function () use ($request, $blog): void {
            $oldSlug = $blog->slug;
            $blog->update($this->prepareData($request, $blog));
            $blog->tags()->sync($request->validated('tag_ids', []));

            if ($oldSlug !== $blog->slug) {
                Redirect::query()->where('old_path', '/blog/'.$blog->slug)->delete();
                Redirect::query()->updateOrCreate(
                    ['old_path' => '/blog/'.$oldSlug],
                    ['new_path' => '/blog/'.$blog->slug, 'status_code' => 301],
                );
            }
        });

        return to_route('admin.blogs.index')->with('message', 'Blog post updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Blog $blog): RedirectResponse
    {
        $blog->delete();

        return to_route('admin.blogs.index')->with('message', 'Blog post moved to trash.');
    }

    /** @return array{categories: Collection, tags: Collection} */
    private function formOptions(): array
    {
        return [
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
            'tags' => Tag::query()->orderBy('name')->get(['id', 'name']),
            'media' => Media::query()->where('mime_type', 'like', 'image/%')->latest()->get(['id', 'original_name']),
        ];
    }

    /** @return array<string, mixed> */
    private function prepareData(BlogRequest $request, ?Blog $blog = null): array
    {
        $data = $request->safe()->except(['tag_ids']);
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title'], $blog);
        $data['featured'] = (bool) ($data['featured'] ?? false);

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = $blog?->published_at ?? now();
        }

        return $data;
    }

    private function uniqueSlug(string $value, ?Blog $blog = null): string
    {
        $slug = Str::slug($value) ?: 'post';
        $baseSlug = $slug;
        $suffix = 2;

        while (Blog::query()->where('slug', $slug)->when($blog, fn ($query) => $query->whereKeyNot($blog->id))->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
