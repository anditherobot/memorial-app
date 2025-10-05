@php $editing = isset($post); @endphp
<form method="POST" action="{{ $editing ? route('admin.updates.update', $post) : route('admin.updates.store') }}" enctype="multipart/form-data" class="space-y-4">
  @csrf
  @if($editing)
    @method('PUT')
  @endif

  <div>
    <x-ui.label for="title">Update Title</x-ui.label>
    <x-ui.input name="title" :value="old('title', $post->title ?? '')" required placeholder="Enter a descriptive title for this update..." error="title" />
  </div>

  <div>
    <x-ui.label for="author_name">Author</x-ui.label>
    <x-ui.input name="author_name" :value="old('author_name', $post->author_name ?? 'Family')" placeholder="Who is publishing this update?" error="author_name" />
  </div>

  <div>
    <x-ui.label for="editor">Update Content</x-ui.label>
    <div class="border border-gray-300 rounded-md overflow-hidden focus-within:ring-2 focus-within:ring-indigo-500 focus-within:border-indigo-500">
      <div class="flex gap-1 p-2 border-b border-gray-200 bg-gray-50 text-sm">
        <button type="button" class="px-2 py-1 border border-gray-200 rounded hover:bg-white transition-colors" onclick="wrapSel('<strong>','</strong>')"><b>B</b></button>
        <button type="button" class="px-2 py-1 border border-gray-200 rounded italic hover:bg-white transition-colors" onclick="wrapSel('<em>','</em>')"><i>I</i></button>
        <button type="button" class="px-2 py-1 border border-gray-200 rounded hover:bg-white transition-colors" onclick="insertLink()">Link</button>
        <button type="button" class="px-2 py-1 border border-gray-200 rounded hover:bg-white transition-colors" onclick="wrapSel('<ul>\n<li>','</li>\n</ul>')">List</button>
      </div>
      <x-ui.textarea id="editor" name="body" rows="10" required
        class="w-full p-3 outline-none resize-none border-0 focus:ring-0 focus:border-transparent"
        placeholder="Write your update content here. You can use the formatting buttons above to add bold text, links, lists, and more..."
      >{{ old('body', $post->body ?? '') }}</x-ui.textarea>
    </div>
    @error('body')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
  </div>

  <div class="grid sm:grid-cols-2 gap-6 items-start">
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-2">Optional cover image</label>
      <input type="file" name="image" accept="image/*" class="mt-1 block w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
      @if(!empty($cover))
        <div class="mt-3 flex items-center gap-3">
          <img src="{{ Storage::disk('public')->url($cover->derivatives()->where('type','thumbnail')->first()?->storage_path ?? $cover->storage_path) }}" class="w-20 h-20 object-cover rounded-lg border" />
          <label class="inline-flex items-center gap-2 text-sm text-gray-600">
            <input type="checkbox" name="remove_image" value="1" class="rounded border-gray-300 text-red-600 focus:ring-red-500" />
            Remove current image
          </label>
        </div>
      @endif
      @error('image')<div class="text-red-600 text-sm mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-3">Publishing Options</label>

        <div class="space-y-3">
          <label class="inline-flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
            <input type="checkbox" name="is_published" value="1" {{ old('is_published', $post->is_published ?? true) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
            <div>
              <div class="text-sm font-medium text-gray-900">Publish immediately</div>
              <div class="text-xs text-gray-500">Make this update visible to visitors</div>
            </div>
          </label>

          <div>
            <x-ui.label for="published_at">Publish date & time</x-ui.label>
            <x-ui.input type="datetime-local" name="published_at" :value="old('published_at', optional($post->published_at ?? now())->format('Y-m-d\\TH:i'))" />
            <div class="text-xs text-gray-500 mt-1">Current time: {{ now()->format('Y-m-d H:i') }}</div>
          </div>
        </div>

        <x-ui.alert variant="info" class="mt-3">
          <svg class="w-4 h-4 text-blue-600 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
          </svg>
          <div class="text-xs">
            <div class="font-medium">About drafts:</div>
            Unpublished updates are saved as drafts and only visible to administrators until published.
          </div>
        </x-ui.alert>
      </div>
    </div>
  </div>

  <div class="border-t border-gray-200 pt-6 mt-8">
    <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
      <div class="text-sm text-gray-600">
        <div class="font-medium">Ready to {{ $editing ? 'save your changes' : 'create your update' }}?</div>
        <div class="text-xs mt-1">{{ $editing ? 'Your changes will be saved with the current settings above.' : 'Check your publishing settings above before creating.' }}</div>
      </div>
      <div class="flex items-center gap-3">
        <x-ui.button-link href="{{ route('admin.updates.index') }}" variant="outline">Cancel</x-ui.button-link>
        <x-ui.button type="submit" variant="primary" class="inline-flex items-center">
          <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          {{ $editing ? 'Save Changes' : 'Create Update' }}
        </x-ui.button>
      </div>
    </div>
  </div>
</form>

<script>
function wrapSel(prefix, suffix) {
  const ta = document.getElementById('editor');
  const start = ta.selectionStart, end = ta.selectionEnd;
  const sel = ta.value.substring(start, end);
  const before = ta.value.substring(0, start);
  const after = ta.value.substring(end);
  ta.value = before + prefix + sel + suffix + after;
  ta.focus();
  ta.selectionStart = start + prefix.length;
  ta.selectionEnd = start + prefix.length + sel.length;
}
function insertLink() {
  const url = prompt('Enter URL');
  if (!url) return;
  wrapSel('<a href="' + url + '">','</a>');
}
</script>
