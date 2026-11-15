<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\File;
use Inertia\Inertia;
use Inertia\Response;

class MediaController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Media/Index', [
            'media' => Media::query()->latest()->paginate(24)->through(fn (Media $media): array => [
                ...$media->only(['id', 'original_name', 'mime_type', 'size_bytes', 'alt_text', 'created_at']),
                'url' => Storage::disk($media->disk)->url($media->path),
            ]),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'file' => ['required', File::types(['jpg', 'jpeg', 'png', 'webp', 'pdf'])->max('10mb')],
            'alt_text' => ['nullable', 'string', 'max:255'],
        ]);
        $file = $data['file'];
        $path = $file->store('portfolio', 'public');
        $dimensions = $file->getMimeType() === 'application/pdf' ? [null, null] : @getimagesize($file->getPathname());
        $media = Media::query()->create([
            'uploaded_by' => $request->user()->id,
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'extension' => $file->guessExtension(),
            'size_bytes' => $file->getSize(),
            'width' => $dimensions[0] ?? null,
            'height' => $dimensions[1] ?? null,
            'alt_text' => $data['alt_text'] ?? null,
        ]);

        return to_route('admin.media.index')->with('message', "{$media->original_name} uploaded.");
    }

    public function destroy(Media $media): RedirectResponse
    {
        Storage::disk($media->disk)->delete($media->path);
        $media->delete();

        return back()->with('message', 'Media deleted.');
    }
}
