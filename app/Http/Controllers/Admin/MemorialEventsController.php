<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessImage;
use App\Models\Media;
use App\Models\MemorialEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class MemorialEventsController extends Controller
{

    public function index()
    {
        $events = MemorialEvent::with('posterMedia')
            ->orderBy('date')
            ->orderBy('time')
            ->get()
            ->groupBy('event_type');

        $eventTypes = MemorialEvent::getEventTypes();

        return view('admin.memorial.events.index', compact('events', 'eventTypes'));
    }

    public function create()
    {
        $eventTypes = MemorialEvent::getEventTypes();
        return view('admin.memorial.events.create', compact('eventTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'event_type' => ['required', Rule::in(['funeral', 'viewing', 'burial', 'repass'])],
            'title' => 'required|string|max:255',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i',
            'venue_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'is_active' => 'boolean',
        ]);

        $posterMediaId = null;

        if ($request->hasFile('poster')) {
            $file = $request->file('poster');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('memorial-events', $filename, 'public');

            $media = Media::create([
                'filename' => $filename,
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'is_public' => true,
            ]);

            ProcessImage::dispatch($media);
            $posterMediaId = $media->id;
        }

        $validated['poster_media_id'] = $posterMediaId;
        $validated['is_active'] = $request->boolean('is_active', true);

        MemorialEvent::create($validated);

        return redirect()
            ->route('memorial.events.index')
            ->with('success', 'Memorial event created successfully.');
    }

    public function show(MemorialEvent $event)
    {
        $event->load('posterMedia');
        return view('admin.memorial.events.show', compact('event'));
    }

    public function edit(MemorialEvent $event)
    {
        $eventTypes = MemorialEvent::getEventTypes();
        return view('admin.memorial.events.edit', compact('event', 'eventTypes'));
    }

    public function update(Request $request, MemorialEvent $event)
    {
        $validated = $request->validate([
            'event_type' => ['required', Rule::in(['funeral', 'viewing', 'burial', 'repass'])],
            'title' => 'required|string|max:255',
            'date' => 'nullable|date',
            'time' => 'nullable|date_format:H:i',
            'venue_name' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'contact_phone' => 'nullable|string|max:50',
            'contact_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
            'poster' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'is_active' => 'boolean',
        ]);

        if ($request->hasFile('poster')) {
            // Delete old poster if exists
            if ($event->posterMedia) {
                Storage::disk('public')->delete($event->posterMedia->path);
                $event->posterMedia->delete();
            }

            $file = $request->file('poster');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('memorial-events', $filename, 'public');

            $media = Media::create([
                'filename' => $filename,
                'path' => $path,
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'is_public' => true,
            ]);

            ProcessImage::dispatch($media);
            $validated['poster_media_id'] = $media->id;
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        $event->update($validated);

        return redirect()
            ->route('memorial.events.index')
            ->with('success', 'Memorial event updated successfully.');
    }

    public function destroy(MemorialEvent $event)
    {
        if ($event->posterMedia) {
            Storage::disk('public')->delete($event->posterMedia->path);
            $event->posterMedia->delete();
        }

        $event->delete();

        return redirect()
            ->route('memorial.events.index')
            ->with('success', 'Memorial event deleted successfully.');
    }
}
