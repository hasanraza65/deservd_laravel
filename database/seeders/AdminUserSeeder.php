<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * The one real, non-demo account this application ships with. Idempotent by
 * design — updateOrCreate() keyed on email means running this seeder any
 * number of times (fresh install, redeploy, `db:seed` re-run) never creates
 * a duplicate admin and never leaves a stale password hash if it changes.
 */
class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@mail.com'],
            [
                'role' => UserRole::Admin,
                'first_name' => 'DESERV\'D',
                'last_name' => 'Admin',
                'phone' => null,
                'password' => Hash::make('f17@AYDS'),
                'status' => UserStatus::Active,
                'email_verified_at' => now(),
            ],
        );

        $this->command->info('Admin account ready: admin@mail.com');
    }
}
