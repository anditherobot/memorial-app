<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create
                            {--email=admin@example.com : The admin email address}
                            {--password=password : The admin password}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an admin user if it does not exist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->option('email');
        $password = $this->option('password');

        // Check if user already exists
        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            $this->info("Admin user already exists: {$email}");

            // Ask if they want to update the password
            if ($this->confirm('Do you want to update the password?', false)) {
                $existingUser->password = Hash::make($password);
                $existingUser->save();
                $this->info("Password updated for: {$email}");
            }

            return Command::SUCCESS;
        }

        // Create new admin user
        User::create([
            'name' => 'Admin',
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Admin user created successfully!");
        $this->info("Email: {$email}");
        $this->info("Password: {$password}");

        return Command::SUCCESS;
    }
}
