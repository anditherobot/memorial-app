<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MemorialContent;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MemorialContentController extends Controller
{
    public function index()
    {
        $contentTypes = MemorialContent::getContentTypes();
        $contents = [];

        // Get existing content for each type
        foreach (array_keys($contentTypes) as $type) {
            $contents[$type] = MemorialContent::findByType($type);
        }

        return view('admin.memorial.content.index', compact('contentTypes', 'contents'));
    }

    public function create()
    {
        $contentTypes = MemorialContent::getContentTypes();
        return view('admin.memorial.content.create', compact('contentTypes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'content_type' => [
                'required',
                Rule::in(array_keys(MemorialContent::getContentTypes())),
                'unique:memorial_content,content_type'
            ],
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        MemorialContent::create($validated);

        return redirect()
            ->route('memorial.content.index')
            ->with('success', 'Memorial content created successfully.');
    }

    public function show(string $contentType)
    {
        $content = MemorialContent::findByType($contentType);

        if (!$content) {
            abort(404);
        }

        return view('admin.memorial.content.show', compact('content'));
    }

    public function edit(MemorialContent $content)
    {
        $contentTypes = MemorialContent::getContentTypes();
        return view('admin.memorial.content.edit', compact('content', 'contentTypes'));
    }

    public function update(Request $request, MemorialContent $content)
    {
        $validated = $request->validate([
            'content_type' => [
                'required',
                Rule::in(array_keys(MemorialContent::getContentTypes())),
                Rule::unique('memorial_content', 'content_type')->ignore($content->id)
            ],
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $content->update($validated);

        return redirect()
            ->route('memorial.content.index')
            ->with('success', 'Memorial content updated successfully.');
    }

    public function destroy(MemorialContent $content)
    {
        $content->delete();

        return redirect()
            ->route('memorial.content.index')
            ->with('success', 'Memorial content deleted successfully.');
    }

    public function editByType(string $contentType)
    {
        if (!array_key_exists($contentType, MemorialContent::getContentTypes())) {
            abort(404);
        }

        $content = MemorialContent::getOrCreateByType($contentType, [
            'title' => '',
            'content' => '',
        ]);

        $contentTypes = MemorialContent::getContentTypes();

        return view('admin.memorial.content.edit-by-type', compact('content', 'contentTypes', 'contentType'));
    }

    public function updateByType(Request $request, string $contentType)
    {
        if (!array_key_exists($contentType, MemorialContent::getContentTypes())) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string',
        ]);

        $content = MemorialContent::getOrCreateByType($contentType);
        $content->update(array_merge($validated, ['content_type' => $contentType]));

        return redirect()
            ->route('memorial.content.index')
            ->with('success', ucfirst(str_replace('_', ' ', $contentType)) . ' updated successfully.');
    }
}
