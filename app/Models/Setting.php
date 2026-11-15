<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $fillable = ['group_name', 'setting_key', 'value', 'value_type'];

    protected function casts(): array
    {
        return ['value' => 'array'];
    }
}
