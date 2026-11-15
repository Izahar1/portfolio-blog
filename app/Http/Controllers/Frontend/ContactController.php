<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Frontend\StoreInquiryRequest;
use App\Models\Inquiry;
use App\Models\Setting;
use App\Notifications\NewInquiryNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Notification;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Frontend/Contact/Index');
    }

    public function store(StoreInquiryRequest $request): RedirectResponse
    {
        $data = $request->safe()->only(['name', 'email', 'subject', 'message']);
        $data['ip_hash'] = $request->ip() ? hash_hmac('sha256', $request->ip(), (string) config('app.key')) : null;
        $inquiry = Inquiry::query()->create($data);
        $recipient = Setting::query()->where('group_name', 'site')->where('setting_key', 'contact_email')->first()?->value ?: config('mail.from.address');

        if (is_string($recipient) && $recipient !== '') {
            Notification::route('mail', $recipient)->notify(new NewInquiryNotification($inquiry));
        }

        return back()->with('message', 'Thank you. Your message has been sent.');
    }
}
