<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class PageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Pages/Index', ['pages' => Page::query()->withCount('sections')->latest()->paginate(20)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Pages/Form', ['page' => null]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        abort_if($data['page']['status'] === 'published' && ! $request->user()->can('publish-content'), 403);
        DB::transaction(function () use ($data): void {
            $page = Page::query()->create([...$data['page'], 'slug' => $this->uniqueSlug($data['page']['slug'] ?? $data['page']['title'])]);
            $page->sections()->createMany($this->sectionsForStorage($data['sections']));
        });

        return to_route('admin.pages.index')->with('message', 'Page created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Page $page): RedirectResponse
    {
        return to_route('admin.pages.edit', $page);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Page $page): Response
    {
        return Inertia::render('Admin/Pages/Form', ['page' => $page->load('sections')]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Page $page): RedirectResponse
    {
        $data = $this->validatedData($request, $page);
        abort_if($data['page']['status'] === 'published' && ! $request->user()->can('publish-content') && $page->status !== 'published', 403);
        DB::transaction(function () use ($page, $data): void {
            $oldSlug = $page->slug;
            $page->update([...$data['page'], 'slug' => $this->uniqueSlug($data['page']['slug'] ?? $data['page']['title'], $page)]);
            $page->sections()->delete();
            $page->sections()->createMany($this->sectionsForStorage($data['sections']));

            if ($oldSlug !== $page->slug) {
                Redirect::query()->where('old_path', '/pages/'.$page->slug)->delete();
                Redirect::query()->updateOrCreate(
                    ['old_path' => '/pages/'.$oldSlug],
                    ['new_path' => '/pages/'.$page->slug, 'status_code' => 301],
                );
            }
        });

        return to_route('admin.pages.index')->with('message', 'Page updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Page $page): RedirectResponse
    {
        $page->delete();

        return to_route('admin.pages.index')->with('message', 'Page moved to trash.');
    }

    /** @return array{page: array<string, mixed>, sections: array<int, array<string, mixed>>} */
    private function validatedData(Request $request, ?Page $page = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($page?->id)],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'template' => ['nullable', 'string', 'max:80'],
            'sections' => ['present', 'array', 'max:30'],
            'sections.*.type' => ['required', Rule::in(['hero', 'about', 'skills', 'featured_projects', 'services', 'testimonials', 'latest_blogs', 'contact_cta', 'rich_text'])],
            'sections.*.heading' => ['nullable', 'string', 'max:255'],
            'sections.*.content_json' => ['required', 'array'],
            'sections.*.is_enabled' => ['boolean'],
        ]);
        $sectionRules = [
            'hero' => ['title' => ['required', 'string', 'max:255'], 'subtitle' => ['nullable', 'string', 'max:2000'], 'cta_label' => ['nullable', 'string', 'max:100'], 'cta_url' => ['nullable', 'regex:/^(https:\/\/|\/)(?!\/)/', 'max:2048'], 'background_media_id' => ['nullable', 'integer', 'exists:media,id']],
            'about' => ['body' => ['nullable', 'string', 'max:10000'], 'image_media_id' => ['nullable', 'integer', 'exists:media,id']],
            'skills' => ['selected_skill_ids' => ['nullable', 'array'], 'selected_skill_ids.*' => ['integer', 'exists:skills,id'], 'display_style' => ['nullable', Rule::in(['list', 'grid'])]],
            'featured_projects' => ['limit' => ['nullable', 'integer', 'between:1,12'], 'selected_project_ids' => ['nullable', 'array'], 'selected_project_ids.*' => ['integer', 'exists:projects,id']],
            'services' => ['selected_service_ids' => ['nullable', 'array'], 'selected_service_ids.*' => ['integer', 'exists:services,id']],
            'testimonials' => ['selected_testimonial_ids' => ['nullable', 'array'], 'selected_testimonial_ids.*' => ['integer', 'exists:testimonials,id']],
            'latest_blogs' => ['limit' => ['nullable', 'integer', 'between:1,12']],
            'contact_cta' => ['body' => ['nullable', 'string', 'max:2000'], 'button_label' => ['nullable', 'string', 'max:100'], 'button_url' => ['nullable', 'regex:/^(https:\/\/|\/)(?!\/)/', 'max:2048']],
            'rich_text' => ['content' => ['nullable', 'string', 'max:20000']],
        ];

        foreach ($validated['sections'] as $index => $section) {
            $validated['sections'][$index]['content_json'] = Validator::make($section['content_json'], $sectionRules[$section['type']])->validate();
        }

        return [
            'page' => array_replace(collect($validated)->except('sections')->all(), [
                'template' => $validated['template'] ?: 'default',
                'published_at' => $validated['status'] === 'published' ? ($page?->published_at ?? now()) : null,
            ]),
            'sections' => $validated['sections'],
        ];
    }

    /** @param array<int, array<string, mixed>> $sections
     * @return array<int, array<string, mixed>>
     */
    private function sectionsForStorage(array $sections): array
    {
        return collect($sections)->values()->map(fn (array $section, int $index): array => [
            'section_key' => (string) Str::uuid(),
            'type' => $section['type'],
            'heading' => $section['heading'] ?? null,
            'content_json' => $section['content_json'],
            'sort_order' => $index,
            'is_enabled' => (bool) ($section['is_enabled'] ?? true),
        ])->all();
    }

    private function uniqueSlug(string $value, ?Page $page = null): string
    {
        $slug = Str::slug($value) ?: 'page';
        $baseSlug = $slug;
        $suffix = 2;

        while (Page::query()->where('slug', $slug)->when($page, fn ($query) => $query->whereKeyNot($page->id))->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
