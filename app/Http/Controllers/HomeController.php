<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the memorial homepage with bio, three photos, and updates.
     */
    public function __invoke(Request $request)
    {
        $bio = "We celebrate the life of Alex Morgan — a beloved friend, artist, and mentor. Alex’s warmth, curiosity, and generosity touched everyone they met. This space gathers memories, photos, and updates for those who wish to pay respects and share stories.";

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

        $updates = [
            [
                'date' => '2025-10-12',
                'title' => 'Memorial Gathering',
                'address' => 'Lakeside Pavilion, 123 Aurora Blvd, Hometown',
                'links' => [
                    ['label' => 'Google Maps', 'url' => 'https://maps.google.com/?q=Lakeside+Pavilion+123+Aurora+Blvd'],
                    ['label' => 'Livestream', 'url' => 'https://example.org/livestream'],
                ],
                'notes' => 'Doors open at 2:00 PM; program at 3:00 PM.',
            ],
            [
                'date' => '2025-10-01',
                'title' => 'Obituary',
                'address' => null,
                'links' => [
                    ['label' => 'Read Online', 'url' => 'https://example.org/obituary'],
                ],
                'notes' => 'Includes details on donations in lieu of flowers.',
            ],
            [
                'date' => '2025-09-20',
                'title' => 'Guestbook & Wishes',
                'address' => null,
                'links' => [
                    ['label' => 'Sign the Wishwall', 'url' => 'https://example.org/wishes'],
                ],
                'notes' => 'Share a memory, photo, or message for the family.',
            ],
        ];

        return view('home', compact('bio', 'photos', 'updates'));
    }
}
