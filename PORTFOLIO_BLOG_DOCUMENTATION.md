# Dynamic Portfolio & Blog CMS — Full Project Documentation

**Version:** 1.0  
**Stack:** Laravel 12, PHP 8.2+, MySQL 8+, Vue 3, Inertia.js, Tailwind CSS, Vite  
**Application:** Single Laravel monolith with public site and protected administration  
**Status:** Implementation specification (not implemented)

## 1. Project Overview

Build a fully dynamic, mobile-first portfolio and blog with a secure CMS. Administrators can manage profile, portfolio projects, blog articles, pages, navigation, media, SEO and global configuration without modifying code.

### Goals
- Dynamic portfolio and blog driven by MySQL.
- Reusable Vue components and separate public/admin layouts.
- Secure role-based access to CMS modules.
- Search-engine friendly public pages, fast performance and accessible UI.
- Maintainable Eloquent models, Form Requests, policies, services and tests.
- Extensible design for multilingual content and newsletters in later releases.

### Non-goals for MVP
- Multi-tenant SaaS, e-commerce checkout, social networking and public user registration.
- Complex drag-and-drop visual page editor; MVP supports configurable ordered sections.

## 2. Technical Stack and Conventions

| Layer | Choice | Notes |
|---|---|---|
| Framework | Laravel 12 | PHP 8.2+; verify production compatibility |
| Database | MySQL 8+ | Use MySQL for all application relational data |
| UI | Vue 3 + Inertia.js | Server-side routes and shared page props |
| Styling | Tailwind CSS | Reusable responsive design tokens |
| Authentication | Laravel starter-kit compatible auth | Session/cookie auth for web + admin |
| Authorization | Policies/Gates, optional Spatie Permission | Enforce server-side |
| Editor | TipTap | Sanitize rendered rich content |
| Uploads | Laravel Storage | Public disk for public assets; private disk for protected files |
| Background jobs | Laravel Queue | Database queue initially |
| Cache | Laravel Cache | Database/file initially; Redis optional |
| Tests | Pest or PHPUnit | Feature, unit and browser tests |
| Deployment | Nginx + PHP-FPM | HTTPS, cron and queue worker |

**Coding conventions:** PSR-12, thin controllers, Form Request validation, policies, transactional writes for multi-table updates, eager loading, named routes, stable slugs, soft deletes where appropriate, and UTC timestamps stored in database.

## 3. Roles and Permissions

| Role | Capabilities |
|---|---|
| Super Admin | All settings, users, roles, content and publishing |
| Admin | Manage portfolio, blog, pages, inquiries and media; no role escalation |
| Editor | Draft/edit content and upload media; publish only when permitted |
| Public Visitor | View published content and submit contact inquiries |

Define permissions: `access-admin`, `manage-users`, `manage-settings`, `manage-profile`, `manage-projects`, `manage-blogs`, `publish-blogs`, `manage-pages`, `manage-media`, `manage-menus`, `manage-seo`, `view-inquiries`, `manage-inquiries`.

Protect all admin routes with `auth`, verified email as appropriate, and a gate/policy. Do not rely on hiding buttons as authorization. Prevent the last super admin from being deleted or demoted.

## 4. Feature Modules

### 4.1 Public Website
- **Home:** hero, introduction, skill highlights, featured projects, services, testimonials, recent posts, contact CTA.
- **About:** bio, profile photo, experience, education, skill list, downloadable resume.
- **Projects:** category/technology filters, search, pagination, detail page, gallery, demo/source links, related projects.
- **Services:** cards and optional detailed descriptions.
- **Blog:** search, category/tag filters, pagination, article pages, author, publish date, reading time, related articles, social sharing.
- **Contact:** validated inquiry form, anti-spam controls, confirmation message, email notification to admin.
- **Pages:** dynamic custom content pages built from approved section types.
- **Navigation:** database-managed header/footer menus and social links.
- **SEO:** meta title/description, Open Graph, canonical URLs, sitemap, robots rules, structured data.

### 4.2 Admin CMS
- Dashboard with published/draft counts, recent inquiries, recent content and activity.
- Profile and resume management.
- Project, gallery, category and technology/skill CRUD.
- Blog post, category, tag and moderated comment CRUD.
- Skills, services, experiences, education and testimonials CRUD.
- Dynamic pages and ordered page sections.
- Media library with alt text and safe upload handling.
- Menus, nested menu items and social links.
- Inquiries inbox with status tracking.
- SEO management, global settings, users/roles and audit log.

### 4.3 Blog Publishing Workflow
`draft -> in_review -> scheduled/published -> archived`

- `published_at` is nullable; public visibility requires `status = published` and `published_at <= now()`.
- Scheduled posts are stored as `scheduled`; a scheduled command promotes eligible posts to `published`.
- Validate ownership/permissions on all transitions.
- Slug uniqueness is enforced by DB index; slug changes should create redirects.
- Preview drafts through authenticated, authorized preview endpoints (or expiring signed URLs).

## 5. Directory Structure

```text
portfolio-blog/
├── app/
│   ├── Console/Commands/PublishScheduledPosts.php
│   ├── Enums/{ContentStatus,InquiryStatus}.php
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Frontend/{Home,About,Project,Blog,Contact,Page}Controller.php
│   │   │   └── Admin/{Dashboard,Profile,Project,Blog,Category,Tag,Skill,
│   │   │       Service,Experience,Education,Testimonial,Page,PageSection,
│   │   │       Media,Inquiry,Menu,SocialLink,Seo,Setting,User}Controller.php
│   │   ├── Requests/{StoreBlogRequest,UpdateBlogRequest,StoreProjectRequest,
│   │   │   UpdateProjectRequest,StoreInquiryRequest,UploadMediaRequest}.php
│   │   └── Middleware/
│   ├── Models/
│   │   ├── User.php
│   │   ├── Profile.php
│   │   ├── Project.php
│   │   ├── ProjectImage.php
│   │   ├── ProjectCategory.php
│   │   ├── Blog.php
│   │   ├── Category.php
│   │   ├── Tag.php
│   │   ├── Comment.php
│   │   ├── Skill.php
│   │   ├── Service.php
│   │   ├── Experience.php
│   │   ├── Education.php
│   │   ├── Testimonial.php
│   │   ├── Page.php
│   │   ├── PageSection.php
│   │   ├── Media.php
│   │   ├── Inquiry.php
│   │   ├── Menu.php
│   │   ├── MenuItem.php
│   │   ├── SocialLink.php
│   │   ├── SeoMeta.php
│   │   ├── Setting.php
│   │   ├── Redirect.php
│   │   └── ActivityLog.php
│   ├── Policies/
│   ├── Services/{BlogService,ProjectService,MediaService,SeoService,
│   │   PageSectionService,SitemapService}.php
│   ├── Jobs/{OptimizeUploadedImage,SendInquiryNotification}.php
│   └── Notifications/NewInquiryNotification.php
├── bootstrap/app.php
├── config/
├── database/{migrations,factories,seeders}/
├── public/
├── resources/
│   ├── css/app.css
│   └── js/
│       ├── app.js
│       ├── Layouts/{PublicLayout,AdminLayout,GuestLayout}.vue
│       ├── Components/{UI,Portfolio,Blog,Admin,PageSections}/
│       ├── Composables/
│       └── Pages/
│           ├── Frontend/{Home,About,Contact}.vue
│           ├── Frontend/{Projects,Blog,Pages}/
│           ├── Admin/{Dashboard,Profile,Projects,Blogs,Categories,Tags,
│           │   Skills,Services,Experiences,Educations,Testimonials,Pages,
│           │   Media,Inquiries,Menus,SEO,Settings,Users}/
│           └── Auth/
├── routes/{web,admin,auth,console}.php
├── storage/
├── tests/{Feature,Unit}/
├── .env.example
├── composer.json
├── package.json
└── vite.config.js
```

Laravel does not automatically load `routes/admin.php`; register it via `bootstrap/app.php` or require it from `routes/web.php` with the desired middleware/prefix. Avoid registering it twice.

## 6. Database Design (MySQL)

**Common fields:** Each core entity uses `id BIGINT UNSIGNED PRIMARY KEY`, `created_at`, `updated_at` unless specified. Add `deleted_at` selectively for soft-deleted entities. All FK columns are unsigned big integers matching primary keys. Use `utf8mb4`, indexes and database constraints.

| Table | Columns (excluding common timestamps/id) | Notes |
|---|---|---|
| `users` | name, email UNIQUE, password, email_verified_at, is_active | Admin accounts |
| `profiles` | user_id UNIQUE FK, full_name, headline, bio LONGTEXT, avatar_media_id FK NULL, resume_media_id FK NULL, location, availability_status | One per owner |
| `projects` | user_id FK, title, slug UNIQUE, summary, description LONGTEXT, thumbnail_media_id FK NULL, demo_url, repository_url, status, featured BOOLEAN, sort_order, published_at | Index status/date |
| `project_images` | project_id FK, media_id FK, caption, sort_order | Ordered gallery |
| `project_categories` | name, slug UNIQUE, description | Categories |
| `project_project_category` | project_id FK, project_category_id FK | Composite UNIQUE |
| `skills` | name, slug UNIQUE, category, icon, proficiency TINYINT NULL, sort_order, is_visible | Skill catalog |
| `project_skill` | project_id FK, skill_id FK | Composite UNIQUE |
| `blogs` | user_id FK, category_id FK NULL, title, slug UNIQUE, excerpt TEXT, content LONGTEXT, featured_media_id FK NULL, status, published_at NULL, featured BOOLEAN, view_count UNSIGNED DEFAULT 0 | Full-text/search strategy |
| `categories` | name, slug UNIQUE, description | Blog categories |
| `tags` | name, slug UNIQUE | Blog tags |
| `blog_tag` | blog_id FK, tag_id FK | Composite UNIQUE |
| `comments` | blog_id FK, user_id FK NULL, parent_id FK NULL, name, email, body TEXT, status, approved_at NULL | Moderation, spam protection |
| `services` | title, slug UNIQUE, description LONGTEXT, icon, sort_order, status | Public services |
| `experiences` | profile_id FK, company, job_title, location, start_date, end_date NULL, is_current, description LONGTEXT, sort_order | Timeline |
| `educations` | profile_id FK, institution, qualification, field_of_study, start_date, end_date NULL, description, sort_order | Timeline |
| `testimonials` | client_name, client_role, company, quote TEXT, rating TINYINT NULL, avatar_media_id FK NULL, status, sort_order | Public approval |
| `pages` | title, slug UNIQUE, status, published_at NULL, template | Custom pages |
| `page_sections` | page_id FK, section_key, type, heading, content_json JSON, sort_order, is_enabled | Section registry |
| `media` | uploaded_by FK, disk, path, original_name, mime_type, extension, size_bytes, width NULL, height NULL, alt_text NULL | Do not expose private assets |
| `inquiries` | name, email, subject, message TEXT, status, ip_hash NULL, responded_at NULL | Minimize PII retention |
| `menus` | name, location UNIQUE | Header/footer |
| `menu_items` | menu_id FK, parent_id FK NULL, label, link_type, url NULL, page_id FK NULL, target, sort_order, is_enabled | Validate nested depth |
| `social_links` | platform, url, icon, sort_order, is_enabled | External URLs |
| `seo_metas` | seoable_type, seoable_id, meta_title, meta_description, og_media_id FK NULL, canonical_url NULL, noindex BOOLEAN | Polymorphic, UNIQUE pair |
| `settings` | group_name, setting_key, value JSON, value_type | UNIQUE(group_name, setting_key) |
| `redirects` | old_path UNIQUE, new_path, status_code | Usually 301/308 |
| `activity_logs` | actor_id FK NULL, action, subject_type, subject_id, changes JSON NULL | Audit trail |

**Spatie Permission (optional):** publish package migrations for `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions`; do not invent incompatible replacement schemas.

### Database Rules
- `slug` columns are unique and indexed.
- Restrict deletion when referenced content must remain; cascade pivot/gallery rows when deleting a parent.
- `status` values should be validated against enums and indexed where used for filtering.
- Use `JSON` for structured section payloads and typed setting values; validate schemas in application code.
- Avoid storing image binaries in MySQL; store metadata/path and serve assets through storage/CDN.
- Include appropriate indexes for `published_at`, `category_id`, `user_id`, `sort_order`, and `(seoable_type, seoable_id)`.
- Prefer an explicit redirect record when a published slug changes.

## 7. Model Relationships

| Model | Relationship |
|---|---|
| User | hasOne Profile; hasMany Blogs; hasMany Projects |
| Profile | belongsTo User; hasMany Experiences; hasMany Educations |
| Project | belongsTo User; hasMany ProjectImages; belongsToMany ProjectCategories; belongsToMany Skills; morphOne SeoMeta |
| ProjectImage | belongsTo Project; belongsTo Media |
| Blog | belongsTo User as author; belongsTo Category; belongsToMany Tags; hasMany Comments; morphOne SeoMeta |
| Category | hasMany Blogs |
| Tag | belongsToMany Blogs |
| Comment | belongsTo Blog; belongsTo User (optional); belongsTo Comment as parent (optional) |
| Page | hasMany PageSections; morphOne SeoMeta |
| PageSection | belongsTo Page |
| Menu | hasMany MenuItems |
| MenuItem | belongsTo Menu; belongsTo MenuItem as parent; hasMany MenuItems as children |
| Media | belongsTo User as uploader |
| Experience/Education | belongsTo Profile |
| SeoMeta | morphTo seoable |

### Example Blog Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id', 'category_id', 'title', 'slug', 'excerpt',
        'content', 'featured_media_id', 'status', 'published_at', 'featured',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'featured' => 'boolean',
        ];
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'blog_tag');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function seo()
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
```

Use the same publication scope for public blog detail and listing queries; never reveal drafts simply because their slug is known.

## 8. Routes

### Public

| Method | Path | Route name | Purpose |
|---|---|---|---|
| GET | `/` | home | Home |
| GET | `/about` | about | About |
| GET | `/projects` | projects.index | Projects |
| GET | `/projects/{slug}` | projects.show | Project detail |
| GET | `/services` | services.index | Services |
| GET | `/blog` | blog.index | Blog index |
| GET | `/blog/category/{slug}` | blog.category | Category |
| GET | `/blog/tag/{slug}` | blog.tag | Tag |
| GET | `/blog/{slug}` | blog.show | Blog detail |
| GET | `/contact` | contact.index | Contact |
| POST | `/contact` | contact.store | Inquiry (throttled) |
| GET | `/sitemap.xml` | sitemap | Sitemap |
| GET | `/pages/{slug}` | pages.show | Dynamic pages |

### Admin (prefix `/admin`, name `admin.`, auth + authorization)

| Method | Path | Purpose |
|---|---|---|
| GET | `/dashboard` | Dashboard |
| Resource | `/projects` | Projects CRUD |
| Resource | `/blogs` | Blogs CRUD |
| Resource | `/categories` | Blog categories |
| Resource | `/tags` | Blog tags |
| Resource | `/skills` | Skills |
| Resource | `/services` | Services |
| Resource | `/experiences` | Experience |
| Resource | `/educations` | Education |
| Resource | `/testimonials` | Testimonials |
| Resource | `/pages` | Pages |
| Resource | `/media` | Media |
| Resource | `/menus` | Menus |
| GET/PATCH | `/profile` | Profile |
| GET/PATCH | `/settings` | Settings |
| GET/PATCH | `/inquiries/{inquiry}` | Inbox/detail/status |
| GET/PATCH | `/seo/{type}/{id}` | SEO metadata |
| Resource | `/users` | Authorized user management |

Additional custom routes: blog publish/unpublish, project feature toggle, page-section reorder, media upload, and approved content preview. Use appropriate HTTP verbs and authorization.

### Sample Routes

```php
// routes/web.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\BlogController;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{slug}', [BlogController::class, 'show'])->name('blog.show');

Route::middleware(['auth', 'verified', 'can:access-admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin.php'));
```

```php
// routes/admin.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\ProjectController;

Route::get('/dashboard', DashboardController::class)->name('dashboard');
Route::resource('blogs', BlogController::class);
Route::resource('projects', ProjectController::class);
```

This example assumes `DashboardController` is invokable. Otherwise use `[DashboardController::class, 'index']`. The `access-admin` gate must be defined before using these routes.

## 9. Admin Sidebar

```text
Dashboard
Portfolio
  Profile
  Projects
  Project Categories
  Skills
  Services
  Experience
  Education
  Testimonials
Content
  Blog Posts
  Blog Categories
  Tags
  Comments
  Pages
  Page Sections
  Media Library
  Menus
  Social Links
Operations
  Contact Inquiries
  SEO & Redirects
  Activity Logs
System
  Users & Roles
  Settings
```

Only show links for authorized users, and enforce permissions again on the server.

## 10. Dynamic Page Builder Specification

Supported MVP section types:
- `hero`: title, subtitle, background_media_id, CTA label/URL.
- `about`: heading, body, image_media_id.
- `skills`: heading, selected_skill_ids, display_style.
- `featured_projects`: heading, limit, selected_project_ids optional.
- `services`: heading, selected_service_ids.
- `testimonials`: heading, selected_testimonial_ids.
- `latest_blogs`: heading, limit.
- `contact_cta`: heading, body, button label/URL.
- `rich_text`: heading, sanitized content.

Each `page_sections` row stores `type`, `content_json`, `sort_order`, `is_enabled`. The backend validates JSON by section type. Vue maps types to a **fixed allowlist** of components, not arbitrary user-supplied component names. Only enabled sections are rendered on published pages. Admin provides edit, preview, reorder and toggle operations.

Example `content_json` for hero:

```json
{
  "title": "Hi, I'm a Laravel Developer",
  "subtitle": "I build fast, maintainable web applications.",
  "cta_label": "View Projects",
  "cta_url": "/projects",
  "background_media_id": null
}
```

## 11. API / Inertia Data Contracts

Prefer Inertia server-side controllers for MVP. Return only necessary public props and paginate collections. Do not serialize entire Eloquent models with hidden or sensitive attributes.

Example blog index props:

```json
{
  "filters": { "search": "", "category": null, "tag": null },
  "posts": {
    "data": [
      {
        "id": 1,
        "title": "Example Post",
        "slug": "example-post",
        "excerpt": "Short summary",
        "featured_image_url": null,
        "published_at": "2026-10-01T12:00:00Z"
      }
    ],
    "links": [],
    "meta": {}
  }
}
```

Pagination shape must match the chosen Laravel resource/transformer strategy; this is an illustrative contract, not a literal default Inertia response.

## 12. Validation and Security

- Validate all writes with Form Requests, including nested page-section JSON.
- Enforce policies and permissions on every mutation and private read.
- Use CSRF protection and session authentication for browser forms.
- Sanitize HTML from TipTap using a trusted HTML sanitizer; escape other user input.
- Validate MIME type, extension, size and dimensions of uploads; generate random paths; reject executable files and unsafe SVG unless sanitized.
- Never trust user-provided storage paths or allow arbitrary file deletion.
- Rate-limit contact, login, comments and search as needed; use honeypot/CAPTCHA if spam persists.
- Validate outbound links, avoid unsafe URL schemes, and use `rel="noopener noreferrer"` for external new-tab links.
- Store secrets in environment variables, never in `settings` or source control.
- Use HTTPS, secure cookies, correct proxy configuration and security headers.
- Protect inquiries and contact details with access controls, retention rules and backups.
- Use parameterized Eloquent queries; whitelist sorting/filter fields.
- Apply database transactions when saving post+tags+SEO or project+images+skills.

## 13. SEO, Performance and Accessibility

### SEO
- Per-page title, description, canonical URL and OG/Twitter metadata.
- XML sitemap for published blogs/projects/pages only.
- Robots rules for admin, drafts, previews and internal search pages.
- Article, Person, WebSite and BreadcrumbList structured data where valid.
- Stable human-readable slugs and redirects on URL changes.
- Image alt text and descriptive internal links.

### Performance
- Eager-load relationships, index common filters, paginate long lists.
- Cache global settings, menus and selected public queries; invalidate cache on edits.
- Optimize images and generate responsive sizes; lazy-load below-fold media.
- Queue image processing and email notifications.
- Build assets for production; measure Core Web Vitals.
- Consider SSR or prerendering for SEO-sensitive Inertia pages; verify crawled HTML in production.

### Accessibility
- Semantic headings, keyboard navigation, visible focus indicators.
- Contrast-compliant light/dark themes, labels, error summaries and alt text.
- Accessible dialogs, menus, search controls and responsive tables.
- Aim for WCAG 2.2 AA on primary journeys.

## 14. Notifications, Queue and Scheduler

- Queue `NewInquiryNotification` to the configured recipient.
- Queue image optimization after upload.
- Run a scheduled command to publish eligible scheduled blog posts.
- Configure the production cron to run `php artisan schedule:run` each minute.
- Run a supervised queue worker and monitor failed jobs.
- Log job failures without leaking personal information or credentials.

## 15. Setup and Local Development

```bash
composer create-project laravel/laravel portfolio-blog "^12.0"
cd portfolio-blog
cp .env.example .env
php artisan key:generate
```

Install a Laravel 12-compatible Vue/Inertia starter kit or configure Vue 3 + Inertia manually; the exact starter-kit installer flags depend on the version selected.

Configure `.env`:

```dotenv
APP_NAME="Portfolio Blog"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=portfolio_blog
DB_USERNAME=portfolio_user
DB_PASSWORD=change_me
QUEUE_CONNECTION=database
CACHE_STORE=database
FILESYSTEM_DISK=public
```

Create the database and least-privilege MySQL user, then:

```bash
php artisan migrate
php artisan db:seed
php artisan storage:link
npm install
npm run dev
php artisan serve
```

For queues and scheduled publishing, configure queue workers and scheduler; create the database queue/cache tables if the chosen starter installation has not already supplied them. Never deploy the example password.

## 16. Testing Plan

| Area | Required checks |
|---|---|
| Auth | Login/logout, reset password, email verification where enabled |
| Authorization | Visitors cannot access admin; editors cannot change roles or settings |
| Portfolio | Project CRUD, images, filtering, published-only public queries |
| Blog | Draft/published/scheduled visibility, tag sync, slug uniqueness, preview authorization |
| Pages | Section schema validation, reorder, disabled section exclusion |
| Media | MIME/size validation, unauthorized deletion, private file handling |
| Contact | Validation, throttling, notification dispatch, inquiry state |
| SEO | Metadata, sitemap excludes drafts, redirects, canonical URLs |
| UI | Mobile/tablet/desktop, keyboard accessibility, empty/loading/error states |
| Reliability | Queue retry behavior, caching invalidation, database rollback |

Example commands:

```bash
php artisan test
npm run build
php artisan route:list
php artisan config:cache
```

Do not use `config:cache` as a substitute for verifying configuration or before `.env` is correctly provisioned.

## 17. Development Phases and Task Checklist

### Phase 1 — Foundation (2–3 days)
- [ ] Initialize Laravel 12 project and Git repository.
- [ ] Configure MySQL and local environment.
- [ ] Install Vue 3, Inertia.js, Tailwind and Vite.
- [ ] Build PublicLayout, AdminLayout and GuestLayout.
- [ ] Implement authentication and role/permission gates.
- [ ] Set up formatting, linting, testing and CI.

### Phase 2 — Database & Models (2–3 days)
- [ ] Create all core migrations and indexes.
- [ ] Create Eloquent models and relationships.
- [ ] Create factories and seeders with demo content.
- [ ] Add Form Requests, policies and enums.
- [ ] Add service classes for complex workflows.

### Phase 3 — Portfolio CMS (4–6 days)
- [ ] Build profile and resume management.
- [ ] Build project CRUD, categories, skills and gallery.
- [ ] Build services, experience, education and testimonials.
- [ ] Implement public homepage, about, projects and services.
- [ ] Add search, filters and pagination.

### Phase 4 — Blog CMS (3–5 days)
- [ ] Build blog CRUD, categories and tags.
- [ ] Integrate rich text editor and safe HTML rendering.
- [ ] Add drafts, scheduled publishing and authorized previews.
- [ ] Build public blog index and article pages.
- [ ] Add search, related posts, reading time and moderation.

### Phase 5 — Dynamic Pages & Settings (3–5 days)
- [ ] Implement page and section CRUD.
- [ ] Implement ordered reusable Vue sections.
- [ ] Build media library and menu management.
- [ ] Build social links and global site settings.
- [ ] Add SEO metadata, sitemap and redirects.

### Phase 6 — QA & Deployment (3–4 days)
- [ ] Run unit, feature and browser tests.
- [ ] Audit authorization, uploads and rich text security.
- [ ] Check accessibility, responsiveness and SEO.
- [ ] Configure production queues, cron, cache and storage.
- [ ] Set up backups, logs, monitoring and CI/CD.
- [ ] Document deployment, rollback and recovery.

**Estimated MVP effort:** 17–26 working days for one experienced developer, excluding extensive custom visual design and major scope changes.

## 18. Deployment Checklist

- [ ] Provision Linux server, PHP-FPM, Nginx, MySQL and HTTPS.
- [ ] Configure secure production `.env`, `APP_DEBUG=false` and `APP_URL`.
- [ ] Run `composer install --no-dev --optimize-autoloader`.
- [ ] Build production frontend assets using `npm ci && npm run build`.
- [ ] Run `php artisan migrate --force` with a verified backup and rollback plan.
- [ ] Configure writable storage/cache directories and storage links.
- [ ] Set up queue worker supervision and scheduler cron.
- [ ] Configure email, rate limits, logs, backups and health checks.
- [ ] Validate permissions, media URLs, sitemap, redirects and error pages.
- [ ] Test restore from backup, not only backup creation.

## 19. Acceptance Criteria

1. Admin can edit all key public portfolio content without changing source files.
2. Admin can create, edit, schedule, publish and archive blog posts.
3. Visitors see only published, eligible content.
4. Projects support multiple images, categories and technologies.
5. Custom pages render ordered, validated, enabled sections.
6. Contact inquiries are validated, rate-limited and visible only to authorized admins.
7. Every CMS write is protected by authorization and validation.
8. Website works across modern mobile and desktop viewports.
9. Public pages expose correct SEO metadata and sitemap entries.
10. Automated tests pass, deployment is repeatable, and backups can be restored.

## 20. Future Enhancements

- Multilingual pages and posts with translation tables.
- Newsletter subscriptions with double opt-in.
- RSS feed and content syndication.
- Advanced analytics dashboard and privacy-friendly tracking.
- Public read-only API and mobile clients.
- Revision history and editorial collaboration.
- Theme presets and richer page section library.
- CDN/object storage, Redis cache and search engine integration when scale requires it.

---

**Implementation note:** This document is a development blueprint. The models, migrations, controllers, Vue pages and tests described above must be implemented; only illustrative code snippets are included here.
