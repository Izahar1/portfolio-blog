<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Profile;
use Inertia\Inertia;
use Inertia\Response;

class AboutController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('Frontend/About', [
            'profile' => Profile::query()->with(['avatar', 'experiences', 'educations'])->first(),
        ]);
    }
}
