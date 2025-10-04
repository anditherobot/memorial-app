<?php

namespace App\Http\Controllers;

use App\Models\MemorialContent;
use App\Models\MemorialEvent;
use App\Models\Post;
use App\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class HomeController extends Controller
{
    /**
     * Display the memorial homepage with dynamic memorial content, events, and updates.
     */
    public function __invoke(Request $request)
    {
        // Get memorial content (guard against missing tables before first migration)
        if (Schema::hasTable('memorial_content')) {
            $memorialName = MemorialContent::findByType('memorial_name');
            $biography = MemorialContent::findByType('bio');
            $memorialDates = MemorialContent::findByType('memorial_dates');
            $contactInfo = MemorialContent::findByType('contact_info');
        } else {
            $memorialName = null;
            $biography = null;
            $memorialDates = null;
            $contactInfo = null;
        }

        // Fallback bio if none configured
        $bio = $biography?->content ?: "We celebrate the life of a beloved friend, family member, and mentor. Their warmth, curiosity, and generosity touched everyone they met. This space gathers memories, photos, and updates for those who wish to pay respects and share stories.";

        // Get recent photos from gallery (limit to 3)
        $galleryPhotos = collect();
        if (Schema::hasTable('media')) {
            $galleryPhotos = Media::where('is_public', true)
                ->whereNotNull('width')
                ->whereNotNull('height')
                ->latest()
                ->limit(3)
                ->get();
        }

        // Default photos if no gallery photos available
        $photos = [];
        if ($galleryPhotos->count() > 0) {
            foreach ($galleryPhotos as $photo) {
                $photos[] = [
                    'url' => Storage::disk('public')->url($photo->storage_path),
                    'alt' => $photo->original_filename,
                    'caption' => $photo->original_filename,
                ];
            }
        } else {
            // Fallback to default memorial images
            $photos = [
                [
                    'url' => asset('images/memorial-flower.svg'),
                    'alt' => 'Floral remembrance illustration',
                    'caption' => 'In bloom, in memory',
                ],
                [
                    'url' => asset('images/memorial-candle.svg'),
                    'alt' => 'Candle of remembrance illustration',
                    'caption' => 'A light that continues',
                ],
                [
                    'url' => asset('images/memorial-dove.svg'),
                    'alt' => 'Dove of peace illustration',
                    'caption' => 'Peace and grace',
                ],
            ];
        }

        // Get upcoming memorial events (limit to 3)
        $upcomingEvents = collect();
        if (Schema::hasTable('memorial_events')) {
            $upcomingEvents = MemorialEvent::active()
                ->where('date', '>=', now()->startOfDay())
                ->orderBy('date')
                ->orderBy('time')
                ->limit(3)
                ->get();
        }

        // Get recent published updates/announcements (limit to 3)
        $recentUpdates = collect();
        if (Schema::hasTable('posts')) {
            $recentUpdates = Post::where('is_published', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->orderByDesc('published_at')
                ->limit(3)
                ->get();
        }

        // Combine events and updates into a unified updates array
        $updates = collect();

        // Add events
        foreach ($upcomingEvents as $event) {
            $updates->push([
                'type' => 'event',
                'date' => $event->date->format('Y-m-d'),
                'title' => $event->title,
                'address' => $event->address,
                'links' => [],
                'notes' => $event->notes,
                'event_type' => $event->event_type,
                'time' => $event->time,
                'venue_name' => $event->venue_name,
            ]);
        }

        // Add recent updates
        foreach ($recentUpdates as $update) {
            $updates->push([
                'type' => 'update',
                'date' => $update->published_at->format('Y-m-d'),
                'title' => $update->title,
                'address' => null,
                'links' => [
                    ['label' => 'Read More', 'url' => route('updates.show', $update)],
                ],
                'notes' => \Str::limit(strip_tags($update->body), 100),
            ]);
        }

        // Sort by date descending and take latest 3
        $updates = $updates->sortByDesc('date')->take(3)->values();

        // Add default items if no content exists
        if ($updates->isEmpty()) {
            $updates = collect([
                [
                    'type' => 'info',
                    'date' => now()->format('Y-m-d'),
                    'title' => 'Guestbook & Wishes',
                    'address' => null,
                    'links' => [
                        ['label' => 'Sign the Wishwall', 'url' => route('wishes.index')],
                    ],
                    'notes' => 'Share a memory, photo, or message for the family.',
                ],
            ]);
        }

        return view('home', compact('bio', 'photos', 'updates', 'memorialName', 'memorialDates', 'contactInfo'));
    }
}
