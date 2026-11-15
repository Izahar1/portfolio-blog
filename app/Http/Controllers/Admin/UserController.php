<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Users/Index', ['users' => User::query()->orderBy('name')->paginate(30)]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', Rule::in(['super_admin', 'admin', 'editor'])],
            'is_active' => ['required', 'boolean'],
        ]);
        $removesAdminAccess = $user->role === 'super_admin' && ($data['role'] !== 'super_admin' || ! $data['is_active']);

        if ($removesAdminAccess && User::query()->where('role', 'super_admin')->where('is_active', true)->count() <= 1) {
            return back()->withErrors(['role' => 'The last active super admin cannot be demoted or disabled.']);
        }

        $user->forceFill($data)->save();

        return back()->with('message', 'User access updated.');
    }
}
