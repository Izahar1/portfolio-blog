<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProjectRequest;
use App\Models\Media;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Redirect;
use App\Models\Skill;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Projects/Index', ['projects' => Project::query()->latest()->paginate(15)]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        return Inertia::render('Admin/Projects/Form', ['project' => null, ...$this->formOptions()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectRequest $request): RedirectResponse
    {
        $data = $this->prepareData($request);
        DB::transaction(function () use ($request, $data): void {
            $project = $request->user()->projects()->create(collect($data)->except(['category_ids', 'skill_ids', 'gallery_media_ids'])->all());
            $this->syncRelations($project, $request->validated());
        });

        return to_route('admin.projects.index')->with('message', 'Project created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): RedirectResponse
    {
        return to_route('admin.projects.edit', $project);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project): Response
    {
        return Inertia::render('Admin/Projects/Form', ['project' => $project->load(['categories:id', 'skills:id', 'images']), ...$this->formOptions()]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectRequest $request, Project $project): RedirectResponse
    {
        DB::transaction(function () use ($request, $project): void {
            $oldSlug = $project->slug;
            $project->update($this->prepareData($request, $project));
            $this->syncRelations($project, $request->validated());

            if ($oldSlug !== $project->slug) {
                Redirect::query()->where('old_path', '/projects/'.$project->slug)->delete();
                Redirect::query()->updateOrCreate(
                    ['old_path' => '/projects/'.$oldSlug],
                    ['new_path' => '/projects/'.$project->slug, 'status_code' => 301],
                );
            }
        });

        return to_route('admin.projects.index')->with('message', 'Project updated.');
    }

    /** @return array{media: Collection, categories: Collection, skills: Collection} */
    private function formOptions(): array
    {
        return [
            'media' => Media::query()->where('mime_type', 'like', 'image/%')->latest()->get(['id', 'original_name']),
            'categories' => ProjectCategory::query()->orderBy('name')->get(['id', 'name']),
            'skills' => Skill::query()->where('is_visible', true)->orderBy('name')->get(['id', 'name']),
        ];
    }

    /** @param array<string, mixed> $data */
    private function syncRelations(Project $project, array $data): void
    {
        $project->categories()->sync($data['category_ids'] ?? []);
        $project->skills()->sync($data['skill_ids'] ?? []);
        $project->images()->delete();
        $project->images()->createMany(collect($data['gallery_media_ids'] ?? [])->values()->map(fn ($mediaId, $index): array => [
            'media_id' => $mediaId,
            'sort_order' => $index,
        ])->all());
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return to_route('admin.projects.index')->with('message', 'Project moved to trash.');
    }

    /** @return array<string, mixed> */
    private function prepareData(ProjectRequest $request, ?Project $project = null): array
    {
        $data = collect($request->validated())->except(['category_ids', 'skill_ids', 'gallery_media_ids'])->all();
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['title'], $project);
        $data['featured'] = (bool) ($data['featured'] ?? false);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        return $data;
    }

    private function uniqueSlug(string $value, ?Project $project = null): string
    {
        $slug = Str::slug($value) ?: 'project';
        $baseSlug = $slug;
        $suffix = 2;

        while (Project::query()->where('slug', $slug)->when($project, fn ($query) => $query->whereKeyNot($project->id))->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
