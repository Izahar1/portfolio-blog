<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class InquiryController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Inquiries/Index', ['inquiries' => Inquiry::query()->latest()->paginate(20)]);
    }

    public function update(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['new', 'read', 'in_progress', 'resolved', 'spam'])]]);
        $inquiry->update([...$data, 'responded_at' => $data['status'] === 'resolved' ? now() : null]);

        return back()->with('message', 'Inquiry status updated.');
    }
}
