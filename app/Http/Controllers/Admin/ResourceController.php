<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Education;
use App\Models\Experience;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\Profile;
use App\Models\ProjectCategory;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\Skill;
use App\Models\SocialLink;
use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ResourceController extends Controller
{
    public function index(string $resource): Response
    {
        $definition = $this->definition($resource);
        $records = $definition['model']::query()->latest()->paginate(25);

        return Inertia::render('Admin/Resources/Index', [
            'resource' => $resource,
            'title' => $definition['title'],
            'records' => $records,
            'columns' => $definition['columns'],
        ]);
    }

    public function create(string $resource): Response
    {
        abort_if($resource === 'comments', 404);

        return $this->form($resource, null);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        abort_if($resource === 'comments', 404);
        $definition = $this->definition($resource);
        $data = $this->validateData($request, $definition);
        $record = new $definition['model'];

        if ($record instanceof Experience || $record instanceof Education) {
            $profile = Profile::query()->first() ?? Profile::query()->create(['user_id' => $request->user()->id, 'full_name' => $request->user()->name]);
            $data['profile_id'] = $profile->id;
        }

        $this->normalizeSlug($data, $record);
        $record->fill($data)->save();

        return to_route('admin.resources.index', $resource)->with('message', $definition['singular'].' created.');
    }

    public function show(string $resource, int $id): RedirectResponse
    {
        return to_route('admin.resources.edit', [$resource, $id]);
    }

    public function edit(string $resource, int $id): Response
    {
        $definition = $this->definition($resource);
        $record = $definition['model']::query()->findOrFail($id);

        return $this->form($resource, $record);
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $definition = $this->definition($resource);
        $record = $definition['model']::query()->findOrFail($id);
        $data = $this->validateData($request, $definition, $record->getKey());
        $this->normalizeSlug($data, $record);
        $record->update($data);

        return to_route('admin.resources.index', $resource)->with('message', $definition['singular'].' updated.');
    }

    public function destroy(string $resource, int $id): RedirectResponse
    {
        $definition = $this->definition($resource);
        $definition['model']::query()->findOrFail($id)->delete();

        return back()->with('message', $definition['singular'].' deleted.');
    }

    private function form(string $resource, ?Model $record): Response
    {
        $definition = $this->definition($resource);
        $options = [];

        if ($resource === 'menu-items') {
            $options = [
                'menus' => Menu::query()->orderBy('name')->get(['id', 'name']),
                'pages' => Page::query()->where('status', 'published')->orderBy('title')->get(['id', 'title']),
                'parents' => MenuItem::query()->whereNull('parent_id')->with('menu:id,name')->orderBy('label')->get(['id', 'menu_id', 'label']),
            ];
        }

        $fields = collect($definition['fields'])->map(function (array $field) use ($options): array {
            $field['options'] = match ($field['name']) {
                'menu_id' => collect($options['menus'] ?? [])->mapWithKeys(fn (Menu $menu): array => [$menu->id => $menu->name])->all(),
                'page_id' => collect($options['pages'] ?? [])->mapWithKeys(fn (Page $page): array => [$page->id => $page->title])->all(),
                'parent_id' => collect($options['parents'] ?? [])->mapWithKeys(fn (MenuItem $item): array => [$item->id => $item->menu->name.' · '.$item->label])->all(),
                default => $field['options'] ?? [],
            };

            return collect($field)->only(['name', 'label', 'type', 'options'])->all();
        });

        return Inertia::render('Admin/Resources/Form', [
            'resource' => $resource,
            'title' => $definition['singular'],
            'fields' => $fields,
            'record' => $record,
            ...$options,
        ]);
    }

    /** @param array{model: class-string<Model>, title: string, singular: string, columns: array<int, string>, fields: array<int, array<string, mixed>>} $definition */
    private function validateData(Request $request, array $definition, ?int $id = null): array
    {
        $rules = [];

        foreach ($definition['fields'] as $field) {
            $rules[$field['name']] = match ([$request->route('resource'), $field['name']]) {
                ['menus', 'location'] => ['required', 'string', 'max:80', Rule::unique('menus', 'location')->ignore($id)],
                ['redirects', 'old_path'] => ['required', 'regex:/^\/(?!\/)/', 'max:255', Rule::unique('redirects', 'old_path')->ignore($id)],
                default => $field['rules'],
            };
        }

        if ($request->route('resource') === 'menu-items') {
            $rules['menu_id'] = ['required', 'integer', 'exists:menus,id'];
            $rules['parent_id'] = ['nullable', 'integer', 'exists:menu_items,id'];
            $rules['page_id'] = [Rule::requiredIf($request->input('link_type') === 'page'), 'nullable', 'integer', 'exists:pages,id'];
            $rules['url'] = [Rule::requiredIf($request->input('link_type') === 'url'), 'nullable', 'regex:/^(https:\/\/|\/)(?!\/)/', 'max:2048'];
            $rules['target'] = ['required', Rule::in(['_self', '_blank'])];
        }

        $data = $request->validate($rules);

        if (($data['status'] ?? null) === 'published' && ! $request->user()->can('publish-content')) {
            $model = $definition['model'];
            $currentRecord = $id ? $model::query()->findOrFail($id) : null;

            abort_unless($currentRecord?->status === 'published', 403);
        }

        if ($request->route('resource') === 'menu-items' && ! empty($data['parent_id'])) {
            $parent = MenuItem::query()->findOrFail($data['parent_id']);

            if ($parent->menu_id !== (int) $data['menu_id'] || $parent->parent_id !== null) {
                throw ValidationException::withMessages(['parent_id' => 'Choose a parent item from the same menu and no deeper than one level.']);
            }
        }

        return $data;
    }

    /** @param array<string, mixed> $data */
    private function normalizeSlug(array &$data, Model $record): void
    {
        if (! array_key_exists('slug', $data)) {
            return;
        }

        $base = Str::slug($data['slug'] ?: ($data['name'] ?? $data['title'] ?? 'item')) ?: 'item';
        $slug = $base;
        $suffix = 2;

        while ($record::query()->where('slug', $slug)->when($record->exists, fn ($query) => $query->whereKeyNot($record->getKey()))->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        $data['slug'] = $slug;
    }

    /** @return array{model: class-string<Model>, title: string, singular: string, columns: array<int, string>, fields: array<int, array<string, mixed>>} */
    private function definition(string $resource): array
    {
        $text = static fn (string $name, string $label, array $rules = ['nullable', 'string', 'max:255']): array => ['name' => $name, 'label' => $label, 'type' => 'text', 'rules' => $rules];
        $textarea = static fn (string $name, string $label, int $max = 10000, ?array $rules = null): array => ['name' => $name, 'label' => $label, 'type' => 'textarea', 'rules' => $rules ?? ['nullable', 'string', "max:{$max}"]];
        $number = static fn (string $name, string $label, int $max = 100): array => ['name' => $name, 'label' => $label, 'type' => 'number', 'rules' => ['nullable', 'integer', 'between:0,'.$max]];
        $checkbox = static fn (string $name, string $label): array => ['name' => $name, 'label' => $label, 'type' => 'checkbox', 'rules' => ['sometimes', 'boolean']];
        $select = static fn (string $name, string $label, array $options): array => ['name' => $name, 'label' => $label, 'type' => 'select', 'options' => $options, 'rules' => ['required', Rule::in(array_keys($options))]];

        $definitions = [
            'project-categories' => [ProjectCategory::class, 'Project categories', 'Project category', ['name', 'slug'], [$text('name', 'Name', ['required', 'string', 'max:120']), $text('slug', 'Slug', ['nullable', 'string', 'max:150']), $textarea('description', 'Description', 2000)]],
            'skills' => [Skill::class, 'Skills', 'Skill', ['name', 'category', 'proficiency'], [$text('name', 'Name', ['required', 'string', 'max:120']), $text('slug', 'Slug', ['nullable', 'string', 'max:150']), $text('category', 'Category'), $text('icon', 'Icon name'), $number('proficiency', 'Proficiency', 100), $number('sort_order', 'Display order', 10000), $checkbox('is_visible', 'Visible publicly')]],
            'services' => [Service::class, 'Services', 'Service', ['title', 'status', 'sort_order'], [$text('title', 'Title', ['required', 'string', 'max:160']), $text('slug', 'Slug', ['nullable', 'string', 'max:180']), $textarea('description', 'Description', 10000, ['required', 'string']), $text('icon', 'Icon name'), $number('sort_order', 'Display order', 10000), $select('status', 'Status', ['draft' => 'Draft', 'published' => 'Published'])]],
            'experiences' => [Experience::class, 'Experience', 'Experience', ['job_title', 'company', 'start_date'], [$text('company', 'Company', ['required', 'string', 'max:160']), $text('job_title', 'Job title', ['required', 'string', 'max:160']), $text('location', 'Location'), ['name' => 'start_date', 'label' => 'Start date', 'type' => 'date', 'rules' => ['nullable', 'date']], ['name' => 'end_date', 'label' => 'End date', 'type' => 'date', 'rules' => ['nullable', 'date']], $checkbox('is_current', 'Current role'), $textarea('description', 'Description'), $number('sort_order', 'Display order', 10000)]],
            'educations' => [Education::class, 'Education', 'Education', ['qualification', 'institution', 'start_date'], [$text('institution', 'Institution', ['required', 'string', 'max:160']), $text('qualification', 'Qualification', ['required', 'string', 'max:160']), $text('field_of_study', 'Field of study'), ['name' => 'start_date', 'label' => 'Start date', 'type' => 'date', 'rules' => ['nullable', 'date']], ['name' => 'end_date', 'label' => 'End date', 'type' => 'date', 'rules' => ['nullable', 'date']], $textarea('description', 'Description', 4000), $number('sort_order', 'Display order', 10000)]],
            'testimonials' => [Testimonial::class, 'Testimonials', 'Testimonial', ['client_name', 'company', 'status'], [$text('client_name', 'Client name', ['required', 'string', 'max:160']), $text('client_role', 'Client role'), $text('company', 'Company'), $textarea('quote', 'Quote', 4000, ['required', 'string', 'max:4000']), $number('rating', 'Rating', 5), $select('status', 'Status', ['draft' => 'Draft', 'published' => 'Published'])]],
            'comments' => [Comment::class, 'Comments', 'Comment', ['name', 'body', 'status'], [$select('status', 'Moderation status', ['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'])]],
            'menus' => [Menu::class, 'Menus', 'Menu', ['name', 'location'], [$text('name', 'Name', ['required', 'string', 'max:120']), $text('location', 'Location', ['required', 'string', 'max:80', 'unique:menus,location'])]],
            'menu-items' => [MenuItem::class, 'Menu items', 'Menu item', ['label', 'link_type', 'sort_order'], [['name' => 'menu_id', 'label' => 'Menu', 'type' => 'select', 'options' => [], 'rules' => ['required', 'integer', 'exists:menus,id']], ['name' => 'parent_id', 'label' => 'Parent item ID (optional)', 'type' => 'number', 'rules' => ['nullable', 'integer', 'exists:menu_items,id']], $text('label', 'Label', ['required', 'string', 'max:120']), $select('link_type', 'Link type', ['url' => 'Custom URL', 'page' => 'Page']), $text('url', 'URL', ['nullable', 'string', 'max:2048']), ['name' => 'page_id', 'label' => 'Page', 'type' => 'select', 'options' => [], 'rules' => ['nullable', 'integer', 'exists:pages,id']], ['name' => 'target', 'label' => 'Target', 'type' => 'select', 'options' => ['_self' => 'Same tab', '_blank' => 'New tab'], 'rules' => ['required', Rule::in(['_self', '_blank'])]], $number('sort_order', 'Display order', 10000), $checkbox('is_enabled', 'Enabled')]],
            'social-links' => [SocialLink::class, 'Social links', 'Social link', ['platform', 'url'], [$text('platform', 'Platform', ['required', 'string', 'max:80']), ['name' => 'url', 'label' => 'URL', 'type' => 'url', 'rules' => ['required', 'url:http,https', 'max:2048']], $text('icon', 'Icon name'), $number('sort_order', 'Display order', 10000), $checkbox('is_enabled', 'Enabled')]],
            'redirects' => [Redirect::class, 'Redirects', 'Redirect', ['old_path', 'new_path', 'status_code'], [$text('old_path', 'Old path', ['required', 'regex:/^\/(?!\/)/', 'max:255', 'unique:redirects,old_path']), $text('new_path', 'New path', ['required', 'regex:/^\/(?!\/)/', 'max:255']), $select('status_code', 'Status code', ['301' => '301 permanent', '308' => '308 permanent'])]],
        ];

        abort_unless(isset($definitions[$resource]), 404);
        [$model, $title, $singular, $columns, $fields] = $definitions[$resource];

        return compact('model', 'title', 'singular', 'columns', 'fields');
    }
}
