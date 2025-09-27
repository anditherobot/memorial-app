@php $editing = isset($post); @endphp
<form method="POST" action="{{ $editing ? route('admin.updates.update', $post) : route('admin.updates.store') }}" enctype="multipart/form-data" class="space-y-4">
  @csrf
  @if($editing)
    @method('PUT')
  @endif

  <div>
    <label class="block text-sm font-medium">Title</label>
    <input type="text" name="title" value="{{ old('title', $post->title ?? '') }}" required class="mt-1 block w-full border rounded p-2" />
    @error('title')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
  </div>

  <div>
    <label class="block text-sm font-medium">Body</label>
    <div class="border rounded">
      <div class="flex gap-2 p-2 border-b text-sm">
        <button type="button" class="px-2 py-1 border rounded" onclick="wrapSel('<strong>','</strong>')"><b>B</b></button>
        <button type="button" class="px-2 py-1 border rounded italic" onclick="wrapSel('<em>','</em>')"><i>I</i></button>
        <button type="button" class="px-2 py-1 border rounded" onclick="insertLink()">Link</button>
        <button type="button" class="px-2 py-1 border rounded" onclick="wrapSel('<ul>\n<li>','</li>\n</ul>')">List</button>
      </div>
      <textarea id="editor" name="body" rows="10" class="w-full p-3 outline-none" required>{{ old('body', $post->body ?? '') }}</textarea>
    </div>
    @error('body')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
  </div>

  <div class="grid sm:grid-cols-2 gap-4 items-start">
    <div>
      <label class="block text-sm font-medium">Optional cover image</label>
      <input type="file" name="image" accept="image/*" class="mt-1 block w-full" />
      @if(!empty($cover))
        <div class="mt-2 flex items-center gap-2">
          <img src="{{ Storage::disk('public')->url(($cover->derivatives()->where('type','thumbnail')->first()->storage_path ?? $cover->storage_path)) }}" class="w-24 h-24 object-cover rounded" />
          <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="remove_image" value="1" /> Remove</label>
        </div>
      @endif
      @error('image')<div class="text-red-600 text-sm">{{ $message }}</div>@enderror
    </div>
    <div class="space-y-2">
      <label class="block text-sm font-medium">Publishing</label>
      <label class="inline-flex items-center gap-2 text-sm"><input type="checkbox" name="is_published" value="1" {{ old('is_published', $post->is_published ?? false) ? 'checked' : '' }} /> Published</label>
      <input type="datetime-local" name="published_at" value="{{ old('published_at', optional($post->published_at ?? null)->format('Y-m-d\TH:i')) }}" class="block border rounded p-2" />
    </div>
  </div>

  <div class="flex items-center gap-2">
    <button class="px-4 py-2 bg-gray-900 text-white rounded">{{ $editing ? 'Save Changes' : 'Create Update' }}</button>
    <a href="{{ route('admin.updates.index') }}" class="px-4 py-2 border rounded">Cancel</a>
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

