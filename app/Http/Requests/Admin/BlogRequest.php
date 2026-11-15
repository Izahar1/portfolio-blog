<?php

namespace App\Http\Requests\Admin;

use App\Models\Blog;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BlogRequest extends FormRequest
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

        $blog = $this->route('blog');
        $status = $this->input('status');

        return ! in_array($status, ['published', 'scheduled'], true)
            || ($blog instanceof Blog && $blog->status === $status)
            || $user->can('publish-content');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $blog = $this->route('blog');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', Rule::unique('blogs', 'slug')->ignore($blog?->id)],
            'excerpt' => ['nullable', 'string', 'max:2000'],
            'content' => ['required', 'string'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'featured_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'tag_ids' => ['array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'status' => ['required', Rule::in(['draft', 'in_review', 'scheduled', 'published', 'archived'])],
            'published_at' => [Rule::when($this->input('status') === 'scheduled', ['required', 'date', 'after:now'], ['nullable', 'date'])],
            'featured' => ['boolean'],
        ];
    }
}
