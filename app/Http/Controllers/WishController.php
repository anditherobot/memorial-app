<?php

namespace App\Http\Controllers;

use App\Models\Wish;
use Illuminate\Http\Request;

class WishController extends Controller
{
    public function index(Request $request)
    {
        $wishes = Wish::query()
            ->where('is_approved', true)
            ->latest()
            ->paginate(10);

        return view('wishes.index', compact('wishes'));
    }

    public function store(Request $request)
    {
        // Honeypot spam trap
        if ($request->filled('website')) {
            return response()->json(['message' => 'Spam detected'], 422);
        }

        $data = $request->validate([
            'name' => ['required','string','max:120'],
            'message' => ['required','string','max:2000'],
        ]);

        $wish = Wish::create([
            'name' => $data['name'],
            'message' => $data['message'],
            'submitted_ip' => $request->ip(),
            'is_approved' => false,
        ]);

        // HTMX support: return a small confirmation snippet
        if ($request->headers->get('HX-Request')) {
            return response()->view('wishes._submitted');
        }

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok', 'id' => $wish->id]);
        }

        return redirect()->route('wishes.index')->with('status', 'Wish submitted and awaiting approval.');
    }

    // Admin endpoints guarded by token middleware
    public function adminIndex()
    {
        $pending = Wish::where('is_approved', false)->latest()->paginate(20);
        return view('wishes.admin', compact('pending'));
    }

    public function approve(Wish $wish)
    {
        $wish->update(['is_approved' => true]);
        return back()->with('status', 'Approved');
    }

    public function destroy(Wish $wish)
    {
        $wish->delete();
        return back()->with('status', 'Deleted');
    }
}
