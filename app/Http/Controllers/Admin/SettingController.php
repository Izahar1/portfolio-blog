<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function edit(): Response
    {
        $settings = Setting::query()->where('group_name', 'site')->get()->mapWithKeys(fn (Setting $setting): array => [$setting->setting_key => $setting->value]);

        return Inertia::render('Admin/Settings/Edit', [
            'settings' => [
                'site_name' => $settings['site_name'] ?? '',
                'seo_title' => $settings['seo_title'] ?? '',
                'seo_description' => $settings['seo_description'] ?? '',
                'contact_email' => $settings['contact_email'] ?? '',
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:120'],
            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:320'],
            'contact_email' => ['nullable', 'email', 'max:255'],
        ]);

        foreach ($data as $key => $value) {
            Setting::query()->updateOrCreate(
                ['group_name' => 'site', 'setting_key' => $key],
                ['value' => $value, 'value_type' => 'string'],
            );
        }

        return back()->with('message', 'Site settings saved.');
    }
}
