<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectCategory;
use App\Models\Redirect;
use App\Models\Skill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'exists:project_categories,slug'],
            'skill' => ['nullable', 'string', 'exists:skills,slug'],
            'search' => ['nullable', 'string', 'max:120'],
        ]);

        return Inertia::render('Frontend/Projects/Index', [
            'projects' => Project::published()->with(['thumbnailMedia:id,disk,path,alt_text', 'categories:id,name,slug', 'skills:id,name,slug'])
                ->when($filters['category'] ?? null, fn ($query, $slug) => $query->whereHas('categories', fn ($categories) => $categories->where('slug', $slug)))
                ->when($filters['skill'] ?? null, fn ($query, $slug) => $query->whereHas('skills', fn ($skills) => $skills->where('slug', $slug)))
                ->when($filters['search'] ?? null, fn ($query, $search) => $query->where(fn ($nested) => $nested->where('title', 'like', "%{$search}%")->orWhere('summary', 'like', "%{$search}%")))
                ->orderBy('sort_order')->latest('published_at')->paginate(12)->withQueryString(),
            'categories' => ProjectCategory::query()->whereHas('projects', fn ($query) => $query->published())->orderBy('name')->get(['id', 'name', 'slug']),
            'skills' => Skill::query()->where('is_visible', true)->whereHas('projects', fn ($query) => $query->published())->orderBy('name')->get(['id', 'name', 'slug']),
            'filters' => $filters,
        ]);
    }

    public function show(string $slug): Response|RedirectResponse
    {
        $project = Project::published()->with(['thumbnailMedia:id,disk,path,alt_text', 'images.media:id,disk,path,alt_text', 'categories:id,name,slug', 'skills:id,name,slug', 'seo'])->where('slug', $slug)->first();

        if (! $project) {
            $redirect = Redirect::query()->where('old_path', '/projects/'.$slug)->first();
            abort_unless($redirect, 404);

            return redirect($redirect->new_path, $redirect->status_code);
        }

        return Inertia::render('Frontend/Projects/Show', [
            'project' => $project,
        ]);
    }
}
