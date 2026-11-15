<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type', 120);
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt_text')->nullable();
            $table->timestamps();
        });

        Schema::create('profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('full_name');
            $table->string('headline')->nullable();
            $table->longText('bio')->nullable();
            $table->foreignId('avatar_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('resume_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('location')->nullable();
            $table->string('availability_status')->nullable();
            $table->timestamps();
        });

        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('tags', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('blogs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->foreignId('featured_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('blog_tag', function (Blueprint $table): void {
            $table->foreignId('blog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
            $table->primary(['blog_id', 'tag_id']);
        });

        Schema::create('comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blog_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->text('body');
            $table->string('status')->default('pending')->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        Schema::create('projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('description')->nullable();
            $table->foreignId('thumbnail_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('demo_url')->nullable();
            $table->string('repository_url')->nullable();
            $table->string('status')->default('draft')->index();
            $table->boolean('featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('project_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category')->nullable();
            $table->string('icon')->nullable();
            $table->unsignedTinyInteger('proficiency')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_visible')->default(true);
            $table->timestamps();
        });

        Schema::create('project_images', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_id')->constrained()->cascadeOnDelete();
            $table->string('caption')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('project_project_category', function (Blueprint $table): void {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['project_id', 'project_category_id']);
        });

        Schema::create('project_skill', function (Blueprint $table): void {
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
            $table->primary(['project_id', 'skill_id']);
        });

        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->longText('description');
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('status')->default('draft')->index();
            $table->timestamps();
        });

        Schema::create('experiences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('company');
            $table->string('job_title');
            $table->string('location')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->boolean('is_current')->default(false);
            $table->longText('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('educations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('profile_id')->constrained()->cascadeOnDelete();
            $table->string('institution');
            $table->string('qualification');
            $table->string('field_of_study')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table): void {
            $table->id();
            $table->string('client_name');
            $table->string('client_role')->nullable();
            $table->string('company')->nullable();
            $table->text('quote');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->foreignId('avatar_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('status')->default('draft')->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('inquiries', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject')->nullable();
            $table->text('message');
            $table->string('status')->default('new')->index();
            $table->string('ip_hash', 64)->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('template')->default('default');
            $table->timestamps();
        });

        Schema::create('page_sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('page_id')->constrained()->cascadeOnDelete();
            $table->string('section_key');
            $table->string('type');
            $table->string('heading')->nullable();
            $table->json('content_json')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table): void {
            $table->id();
            $table->string('group_name');
            $table->string('setting_key');
            $table->json('value')->nullable();
            $table->string('value_type')->default('string');
            $table->timestamps();
            $table->unique(['group_name', 'setting_key']);
        });

        Schema::create('menus', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('location')->unique();
            $table->timestamps();
        });

        Schema::create('menu_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('menu_items')->nullOnDelete();
            $table->string('label');
            $table->string('link_type')->default('url');
            $table->string('url')->nullable();
            $table->foreignId('page_id')->nullable()->constrained()->nullOnDelete();
            $table->string('target')->default('_self');
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('social_links', function (Blueprint $table): void {
            $table->id();
            $table->string('platform');
            $table->string('url');
            $table->string('icon')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();
        });

        Schema::create('seo_metas', function (Blueprint $table): void {
            $table->id();
            $table->morphs('seoable');
            $table->string('meta_title')->nullable();
            $table->string('meta_description', 320)->nullable();
            $table->foreignId('og_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('canonical_url')->nullable();
            $table->boolean('noindex')->default(false);
            $table->timestamps();
            $table->unique(['seoable_type', 'seoable_id']);
        });

        Schema::create('redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('old_path')->unique();
            $table->string('new_path');
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->timestamps();
        });

        Schema::create('activity_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->nullableMorphs('subject');
            $table->json('changes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('redirects');
        Schema::dropIfExists('seo_metas');
        Schema::dropIfExists('social_links');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('menus');
        Schema::dropIfExists('page_sections');
        Schema::dropIfExists('pages');
        Schema::dropIfExists('inquiries');
        Schema::dropIfExists('testimonials');
        Schema::dropIfExists('educations');
        Schema::dropIfExists('experiences');
        Schema::dropIfExists('services');
        Schema::dropIfExists('project_skill');
        Schema::dropIfExists('project_project_category');
        Schema::dropIfExists('project_images');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('project_categories');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('blog_tag');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('profiles');
        Schema::dropIfExists('media');
    }
};
