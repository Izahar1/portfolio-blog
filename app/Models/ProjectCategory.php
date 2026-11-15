<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProjectCategory extends Model
{
    protected $fillable = ['name', 'slug', 'description'];

    public function projects(): BelongsToMany
    {
        return $this->belongsToMany(Project::class, 'project_project_category');
    }
}
