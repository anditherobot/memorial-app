<?php

namespace App\Console\Commands;

use App\Models\Media;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanupOrphanedMedia extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:cleanup-orphaned';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove orphaned media records where files no longer exist';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orphaned = Media::all()->filter(function ($media) {
            return !Storage::disk('public')->exists($media->storage_path);
        });

        $this->info("Found {$orphaned->count()} orphaned media records.");

        foreach ($orphaned as $media) {
            $this->info("Deleting orphaned record: {$media->original_filename} (path: {$media->storage_path})");

            // Delete derivatives first
            $media->derivatives()->delete();

            // Delete media record
            $media->delete();
        }

        $this->info('Cleanup complete!');
    }
}
