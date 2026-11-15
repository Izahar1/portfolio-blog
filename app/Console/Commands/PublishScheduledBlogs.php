<?php

namespace App\Console\Commands;

use App\Models\Blog;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:publish-scheduled-blogs')]
#[Description('Publish blog posts whose scheduled time has arrived')]
class PublishScheduledBlogs extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $publishedCount = Blog::query()
            ->where('status', 'scheduled')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->update(['status' => 'published', 'updated_at' => now()]);

        $this->info("Published {$publishedCount} scheduled blog post(s).");

        return self::SUCCESS;
    }
}
