<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['client_name', 'client_role', 'company', 'quote', 'rating', 'avatar_media_id', 'status', 'sort_order'];
}
