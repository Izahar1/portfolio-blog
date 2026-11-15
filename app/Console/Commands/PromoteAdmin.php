<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:promote-admin {email : Email address of the existing account}')]
#[Description('Grant an existing account super admin access')]
class PromoteAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $user = User::query()->where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error('No user was found with that email address.');

            return self::FAILURE;
        }

        $user->forceFill(['role' => 'super_admin', 'is_active' => true])->save();
        $this->info("{$user->email} can now access the admin CMS.");

        return self::SUCCESS;
    }
}
