<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Profile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Admin/Profile/Edit', [
            'profile' => Profile::query()->with(['avatar', 'resume'])->first(),
            'media' => Media::query()->latest()->get(['id', 'original_name', 'mime_type']),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'headline' => ['nullable', 'string', 'max:255'],
            'bio' => ['nullable', 'string', 'max:20000'],
            'location' => ['nullable', 'string', 'max:255'],
            'availability_status' => ['nullable', 'string', 'max:100'],
            'avatar_media_id' => ['nullable', 'integer', 'exists:media,id'],
            'resume_media_id' => ['nullable', 'integer', 'exists:media,id'],
        ]);
        $profile = Profile::query()->first();

        if ($profile) {
            $profile->update($data);
        } else {
            Profile::query()->create([...$data, 'user_id' => $request->user()->id]);
        }

        return back()->with('message', 'Profile updated.');
    }
}
