<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
        Gate::define('access-admin', fn (User $user): bool => $user->canAccessAdmin());
        Gate::define('manage-settings', fn (User $user): bool => $user->is_active && $user->role === 'super_admin');
        Gate::define('manage-users', fn (User $user): bool => $user->is_active && $user->role === 'super_admin');
        Gate::define('publish-content', fn (User $user): bool => $user->is_active && in_array($user->role, ['super_admin', 'admin'], true));
        Gate::define('manage-profile', fn (User $user): bool => $user->is_active && in_array($user->role, ['super_admin', 'admin'], true));
        Gate::define('manage-inquiries', fn (User $user): bool => $user->is_active && in_array($user->role, ['super_admin', 'admin'], true));
        Gate::define('manage-seo', fn (User $user): bool => $user->is_active && in_array($user->role, ['super_admin', 'admin'], true));
    }
}
