<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Profile;
use App\Models\Project;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Frontend/Home', [
            'profile' => Profile::query()->with('avatar')->first(),
            'projects' => Project::published()->with('thumbnailMedia:id,disk,path,alt_text')->where('featured', true)->orderBy('sort_order')->limit(3)->get(),
            'posts' => Blog::published()->with(['author:id,name', 'category:id,name,slug', 'featuredMedia:id,disk,path,alt_text'])->latest('published_at')->limit(3)->get(),
        ]);
    }
}
