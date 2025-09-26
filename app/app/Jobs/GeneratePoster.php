<?php

namespace App\Jobs;

use App\Models\Media;
use App\Models\MediaDerivative;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class GeneratePoster implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $mediaId) {}

    public function handle(): void
    {
        $media = Media::find($this->mediaId);
        if (!$media || !str_starts_with($media->mime_type, 'video/')) return;

        // Placeholder: Create an empty marker file as "poster" if we can't transcode.
        $targetDir = 'media/derivatives/'.$media->id;
        $posterPath = $targetDir.'/poster.jpg';
        if (!\Storage::disk('public')->exists($posterPath)) {
            \Storage::disk('public')->put($posterPath, '');
        }

        MediaDerivative::updateOrCreate(
            ['media_id' => $media->id, 'type' => 'poster', 'storage_path' => $posterPath],
            ['width' => null, 'height' => null, 'size_bytes' => \Storage::disk('public')->size($posterPath)]
        );
    }
}
