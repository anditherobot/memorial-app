<?php

namespace Database\Seeders;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $adminUser = User::where('is_admin', true)->first();

        $tasks = [
            [
                'title' => 'Add image upload to posts',
                'description' => 'Allow admins to attach images when creating/editing posts in the updates section',
                'status' => 'todo',
                'priority' => 'high',
                'category' => 'Feature',
                'assigned_to' => $adminUser?->id,
                'due_date' => now()->addDays(7),
                'notes' => 'This will improve the visual appeal of updates'
            ],
            [
                'title' => 'Email notifications for new wishes',
                'description' => 'Send email notifications to admin when new wishes are submitted',
                'status' => 'todo',
                'priority' => 'medium',
                'category' => 'Feature',
                'due_date' => now()->addDays(14)
            ],
            [
                'title' => 'Setup staging environment',
                'description' => 'Create a staging environment for testing changes before production deployment',
                'status' => 'in_progress',
                'priority' => 'high',
                'category' => 'DevOps',
                'assigned_to' => $adminUser?->id,
                'notes' => 'Need to setup CI/CD pipeline as well'
            ],
            [
                'title' => 'Add search functionality',
                'description' => 'Add search capability to the gallery and updates sections',
                'status' => 'todo',
                'priority' => 'medium',
                'category' => 'Enhancement',
                'due_date' => now()->addDays(21)
            ],
            [
                'title' => 'Fix mobile responsiveness',
                'description' => 'Gallery layout needs improvement on mobile devices',
                'status' => 'todo',
                'priority' => 'urgent',
                'category' => 'Bug',
                'due_date' => now()->addDays(3),
                'notes' => 'Users reporting layout issues on phones'
            ],
            [
                'title' => 'Add backup automation',
                'description' => 'Implement automated daily backups of the database and uploaded media',
                'status' => 'blocked',
                'priority' => 'high',
                'category' => 'DevOps',
                'notes' => 'Waiting for server access credentials'
            ],
            [
                'title' => 'Setup analytics tracking',
                'description' => 'Add Google Analytics or similar to track site usage',
                'status' => 'completed',
                'priority' => 'low',
                'category' => 'Enhancement',
                'assigned_to' => $adminUser?->id
            ],
            [
                'title' => 'Create user documentation',
                'description' => 'Write documentation for admins on how to use the admin panel',
                'status' => 'todo',
                'priority' => 'low',
                'category' => 'Documentation',
                'due_date' => now()->addDays(30)
            ],
            [
                'title' => 'Optimize image loading',
                'description' => 'Implement lazy loading for gallery images to improve page load speed',
                'status' => 'in_progress',
                'priority' => 'medium',
                'category' => 'Performance',
                'assigned_to' => $adminUser?->id,
                'notes' => 'Research progressive image loading techniques'
            ],
            [
                'title' => 'Add comment moderation',
                'description' => 'Allow visitors to leave comments on updates with admin moderation',
                'status' => 'todo',
                'priority' => 'low',
                'category' => 'Feature',
                'due_date' => now()->addDays(45)
            ]
        ];

        foreach ($tasks as $taskData) {
            Task::create($taskData);
        }
    }
}