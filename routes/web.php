<?php

use App\Http\Controllers\Admin\BlogController as AdminBlogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\InquiryController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\ProfileController as AdminProfileController;
use App\Http\Controllers\Admin\ProjectController as AdminProjectController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TaxonomyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Frontend\AboutController;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\ContactController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\PageController;
use App\Http\Controllers\Frontend\ProjectController;
use App\Http\Controllers\Frontend\RedirectController;
use App\Http\Controllers\Frontend\ServiceController;
use App\Http\Controllers\Frontend\SitemapController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/about', AboutController::class)->name('about');
Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{slug}', [ProjectController::class, 'show'])->name('projects.show');
Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/category/{slug}', [BlogController::class, 'category'])->name('blog.category');
Route::get('/blog/tag/{slug}', [BlogController::class, 'tag'])->name('blog.tag');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/contact', [ContactController::class, 'index'])->name('contact.index');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', fn () => response("User-agent: *\nDisallow: /admin\nDisallow: /login\nDisallow: /blog?search=\nSitemap: ".route('sitemap')."\n", 200, ['Content-Type' => 'text/plain']))->name('robots');
Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');
Route::match(['get', 'post'], '/register', fn () => abort(404))->name('registration.disabled');

Route::get('/dashboard', function () {
    return to_route('admin.dashboard');
})->middleware(['auth', 'verified', 'can:access-admin'])->name('dashboard');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'verified', 'can:access-admin'])->group(function (): void {
    Route::get('/dashboard', AdminDashboardController::class)->name('dashboard');
    Route::resource('blogs', AdminBlogController::class);
    Route::resource('projects', AdminProjectController::class);
    Route::resource('pages', AdminPageController::class);
    Route::get('/resources/{resource}', [ResourceController::class, 'index'])->name('resources.index');
    Route::get('/resources/{resource}/create', [ResourceController::class, 'create'])->name('resources.create');
    Route::post('/resources/{resource}', [ResourceController::class, 'store'])->name('resources.store');
    Route::get('/resources/{resource}/{id}/edit', [ResourceController::class, 'edit'])->whereNumber('id')->name('resources.edit');
    Route::put('/resources/{resource}/{id}', [ResourceController::class, 'update'])->whereNumber('id')->name('resources.update');
    Route::patch('/resources/{resource}/{id}', [ResourceController::class, 'update'])->whereNumber('id')->name('resources.patch');
    Route::delete('/resources/{resource}/{id}', [ResourceController::class, 'destroy'])->whereNumber('id')->name('resources.destroy');
    Route::middleware('can:manage-profile')->group(function (): void {
        Route::get('/profile', [AdminProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [AdminProfileController::class, 'update'])->name('profile.update');
    });
    Route::get('/media', [MediaController::class, 'index'])->name('media.index');
    Route::post('/media', [MediaController::class, 'store'])->name('media.store');
    Route::delete('/media/{media}', [MediaController::class, 'destroy'])->name('media.destroy');
    Route::middleware('can:manage-users')->group(function (): void {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    });
    Route::middleware('can:manage-settings')->group(function (): void {
        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::patch('/settings', [SettingController::class, 'update'])->name('settings.update');
    });
    Route::middleware('can:manage-seo')->group(function (): void {
        Route::get('/seo/{type}/{id}', [SeoController::class, 'edit'])->whereNumber('id')->name('seo.edit');
        Route::patch('/seo/{type}/{id}', [SeoController::class, 'update'])->whereNumber('id')->name('seo.update');
    });
    Route::get('/taxonomies/{type}', [TaxonomyController::class, 'index'])->whereIn('type', ['categories', 'tags'])->name('taxonomies.index');
    Route::post('/taxonomies/{type}', [TaxonomyController::class, 'store'])->whereIn('type', ['categories', 'tags'])->name('taxonomies.store');
    Route::put('/taxonomies/{type}/{id}', [TaxonomyController::class, 'update'])->whereIn('type', ['categories', 'tags'])->name('taxonomies.update');
    Route::delete('/taxonomies/{type}/{id}', [TaxonomyController::class, 'destroy'])->whereIn('type', ['categories', 'tags'])->name('taxonomies.destroy');
    Route::middleware('can:manage-inquiries')->group(function (): void {
        Route::get('/inquiries', [InquiryController::class, 'index'])->name('inquiries.index');
        Route::patch('/inquiries/{inquiry}', [InquiryController::class, 'update'])->name('inquiries.update');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

Route::get('/{path}', RedirectController::class)->where('path', '.*')->name('redirects.resolve');
