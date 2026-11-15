<?php

namespace App\Http\Requests\Admin;

use App\Models\Project;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user?->canAccessAdmin()) {
            return false;
        }

        $project = $this->route('project');

        return $this->input('status') !== 'published'
            || ($project instanceof Project && $project->status === 'published')
            || $user->can('publish-content');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('projects', 'slug')->ignore($project?->id)],
            'summary' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string'],
            'thumbnail_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'demo_url' => ['nullable', 'url:http,https', 'max:2048'],
            'repository_url' => ['nullable', 'url:http,https', 'max:2048'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'featured' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
            'published_at' => ['nullable', 'date'],
            'category_ids' => ['sometimes', 'array'],
            'category_ids.*' => ['integer', 'exists:project_categories,id'],
            'skill_ids' => ['sometimes', 'array'],
            'skill_ids.*' => ['integer', 'exists:skills,id'],
            'gallery_media_ids' => ['sometimes', 'array'],
            'gallery_media_ids.*' => ['integer', Rule::exists('media', 'id')->where(fn ($query) => $query->where('mime_type', 'like', 'image/%'))],
        ];
    }
}
