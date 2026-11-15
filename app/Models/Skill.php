<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Skill extends Model
{
    protected $fillable = ['name', 'slug', 'category', 'icon', 'proficiency', 'sort_order', 'is_visible'];

    protected function casts(): array
    {
        return ['is_visible' => 'boolean'];
    }

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_skill');
    }
}
