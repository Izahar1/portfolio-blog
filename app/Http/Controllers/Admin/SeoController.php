<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Page;
use App\Models\Project;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SeoController extends Controller
{
    public function edit(string $type, int $id): Response
    {
        $record = $this->record($type, $id);

        return Inertia::render('Admin/SEO/Edit', [
            'type' => $type,
            'record' => $record->only(['id', 'title']),
            'seo' => $record->seo?->only(['meta_title', 'meta_description', 'canonical_url', 'noindex']) ?? [],
        ]);
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $data = $request->validate([
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'canonical_url' => ['nullable', 'url:http,https', 'max:2048'],
            'noindex' => ['boolean'],
        ]);
        $record = $this->record($type, $id);
        $record->seo()->updateOrCreate([], $data);

        return back()->with('message', 'SEO metadata saved.');
    }

    private function record(string $type, int $id): Model
    {
        $model = match ($type) {
            'blogs' => Blog::class,
            'projects' => Project::class,
            'pages' => Page::class,
            default => abort(404),
        };

        return $model::query()->with('seo')->findOrFail($id);
    }
}
