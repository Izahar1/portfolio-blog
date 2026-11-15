<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Inquiry;
use App\Models\Project;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        return Inertia::render('Admin/Dashboard', [
            'counts' => [
                'posts' => Blog::query()->count(),
                'drafts' => Blog::query()->where('status', 'draft')->count(),
                'projects' => Project::query()->count(),
                'newInquiries' => Inquiry::query()->where('status', 'new')->count(),
            ],
            'recentInquiries' => Inquiry::query()->latest()->limit(5)->get(['id', 'name', 'subject', 'status', 'created_at']),
        ]);
    }
}
