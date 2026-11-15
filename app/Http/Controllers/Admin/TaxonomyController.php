<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TaxonomyController extends Controller
{
    public function index(string $type): Response
    {
        $model = $this->model($type);
        $items = $model::query()->orderBy('name')->paginate(30);

        return Inertia::render('Admin/Taxonomies/Index', ['type' => $type, 'items' => $items]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $data = $request->validate($this->rules($type));
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name'], $type);
        $model = $this->model($type);
        $model::query()->create($data);

        return back()->with('message', $this->label($type).' created.');
    }

    public function update(Request $request, string $type, int $id): RedirectResponse
    {
        $model = $this->model($type);
        $item = $model::query()->findOrFail($id);
        $data = $request->validate($this->rules($type, $item->id));
        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name'], $type, $item->id);
        $item->update($data);

        return back()->with('message', $this->label($type).' updated.');
    }

    public function destroy(string $type, int $id): RedirectResponse
    {
        $model = $this->model($type);
        $model::query()->findOrFail($id)->delete();

        return back()->with('message', $this->label($type).' deleted.');
    }

    /** @return class-string<Category|Tag> */
    private function model(string $type): string
    {
        return match ($type) {
            'categories' => Category::class,
            'tags' => Tag::class,
            default => abort(404),
        };
    }

    /** @return array<string, array<int, mixed>|string> */
    private function rules(string $type, ?int $id = null): array
    {
        $table = $type === 'categories' ? 'categories' : 'tags';
        $rules = [
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'string', 'max:150', Rule::unique($table, 'slug')->ignore($id)],
        ];

        if ($type === 'categories') {
            $rules['description'] = ['nullable', 'string', 'max:2000'];
        }

        return $rules;
    }

    private function uniqueSlug(string $value, string $type, ?int $id = null): string
    {
        $model = $this->model($type);
        $slug = Str::slug($value) ?: ($type === 'categories' ? 'category' : 'tag');
        $baseSlug = $slug;
        $suffix = 2;

        while ($model::query()->where('slug', $slug)->when($id, fn ($query) => $query->whereKeyNot($id))->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function label(string $type): string
    {
        return $type === 'categories' ? 'Category' : 'Tag';
    }
}
