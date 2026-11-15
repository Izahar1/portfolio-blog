<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Frontend/Services/Index', [
            'services' => Service::query()->where('status', 'published')->orderBy('sort_order')->get(),
        ]);
    }
}
