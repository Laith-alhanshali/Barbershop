<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class ProductionBootstrapSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates an initial super admin user from environment variables.
     * It is production-safe and will not overwrite existing users.
     */
    public function run(): void
    {
        // Ensure the super_admin role exists
        $role = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => 'web'
        ]);

        // Read bootstrap user data from environment
        $name = env('BOOTSTRAP_ADMIN_NAME', 'Super Admin');
        $email = env('BOOTSTRAP_ADMIN_EMAIL');
        $password = env('BOOTSTRAP_ADMIN_PASSWORD');

        // Validate required fields
        if (empty($email) || empty($password)) {
            $this->command->warn('⚠️  Bootstrap skipped: BOOTSTRAP_ADMIN_EMAIL and BOOTSTRAP_ADMIN_PASSWORD must be set in .env');
            $this->command->warn('   Add these keys to your .env file and run this seeder again.');
            return;
        }

        // Create or find the user
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ]
        );

        // Only hash and update password if user was just created
        if (!$user->wasRecentlyCreated) {
            $this->command->info("ℹ️  User already exists: {$email}");
            $this->command->info('   Password was NOT changed. Only ensuring role assignment...');
        } else {
            $this->command->info("✓ Created new user: {$email}");
        }

        // Ensure the user has the super_admin role
        if (!$user->hasRole('super_admin')) {
            $user->assignRole('super_admin');
            $this->command->info('✓ Assigned super_admin role');
        } else {
            $this->command->info('✓ User already has super_admin role');
        }

        $this->command->newLine();
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
        $this->command->info("✅ Bootstrap complete!");
        $this->command->info("   Email: {$email}");
        $this->command->info("   Role: super_admin");
        $this->command->newLine();
        $this->command->warn("🔒 SECURITY REMINDER:");
        $this->command->warn("   1. Change the password after first login");
        $this->command->warn("   2. Optionally remove BOOTSTRAP_ADMIN_* keys from .env");
        $this->command->info("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━");
    }
}
