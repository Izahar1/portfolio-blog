<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PageSection extends Model
{
    protected $fillable = ['page_id', 'section_key', 'type', 'heading', 'content_json', 'sort_order', 'is_enabled'];

    protected function casts(): array
    {
        return ['content_json' => 'array', 'is_enabled' => 'boolean'];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}
