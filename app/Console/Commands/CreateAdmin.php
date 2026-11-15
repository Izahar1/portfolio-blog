<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

#[Signature('app:create-admin {email : Administrator email address} {name : Administrator name}')]
#[Description('Create the first super admin account')]
class CreateAdmin extends Command
{
    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $name = (string) $this->argument('name');
        $password = $this->secret('Choose a password');
        $validator = Validator::make(compact('name', 'email', 'password'), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', Password::min(12)->mixedCase()->numbers()],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::query()->create(['name' => $name, 'email' => $email, 'password' => Hash::make($password)]);
        $user->forceFill(['role' => 'super_admin', 'is_active' => true, 'email_verified_at' => now()])->save();
        $this->info("Administrator account {$email} created.");

        return self::SUCCESS;
    }
}
