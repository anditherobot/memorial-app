
<div class="p-6 bg-white border rounded-lg shadow-sm space-y-4">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $title }}</label>
            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600">
                        <label for="file-upload" class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500">
                            <span>Upload a file</span>
                            <input id="file-upload" name="{{ $inputName }}" type="file" accept="{{ $acceptedFileTypes }}" required class="sr-only" onchange="previewImage(this)" />
                        </label>
                        <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">PNG, JPG, GIF up to {{ $maxFileSizeMb }}MB</p>
                </div>
            </div>
            @error($inputName)
                <div class="text-red-600 text-sm mt-2">{{ $message }}</div>
            @enderror
        </div>

        <!-- Image Preview -->
        <div id="image-preview" class="hidden">
            <label class="block text-sm font-medium text-gray-700 mb-2">Preview</label>
            <div class="flex items-start space-x-4">
                <img id="preview-image" class="h-32 w-32 object-cover rounded-lg border" />
                <div class="flex-1">
                    <div id="image-info" class="text-sm text-gray-600 space-y-1"></div>
                    <button type="button" onclick="clearPreview()" class="mt-2 text-sm text-red-600 hover:text-red-500">Remove</button>
                </div>
            </div>
        </div>

        {{ $slot }}

        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <input id="is_public" name="is_public" type="checkbox" checked class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                <label for="is_public" class="ml-2 block text-sm text-gray-900">Make photo public (visible in gallery)</label>
            </div>
            <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                </svg>
                Upload Photo
            </button>
        </div>
    </form>
</div>

<script>
    function previewImage(input) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('image-preview');
                const previewImage = document.getElementById('preview-image');
                const imageInfo = document.getElementById('image-info');

                previewImage.src = e.target.result;
                preview.classList.remove('hidden');

                // Display file information
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                imageInfo.innerHTML = `
                    <div><strong>File:</strong> ${file.name}</div>
                    <div><strong>Size:</strong> ${fileSize} MB</div>
                    <div><strong>Type:</strong> ${file.type}</div>
                `;
            };
            reader.readAsDataURL(file);
        }
    }

    function clearPreview() {
        const preview = document.getElementById('image-preview');
        const fileInput = document.getElementById('file-upload');
        const previewImage = document.getElementById('preview-image');
        const imageInfo = document.getElementById('image-info');

        preview.classList.add('hidden');
        fileInput.value = '';
        previewImage.src = '';
        imageInfo.innerHTML = '';
    }

    // Enhanced drag and drop functionality
    document.addEventListener('DOMContentLoaded', function() {
        const dropZone = document.querySelector('.border-dashed');
        const fileInput = document.getElementById('file-upload');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
            document.body.addEventListener(eventName, preventDefaults, false);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, highlight, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, unhighlight, false);
        });

        dropZone.addEventListener('drop', handleDrop, false);

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        function highlight(e) {
            dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
        }

        function unhighlight(e) {
            dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
        }

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;

            if (files.length > 0) {
                fileInput.files = files;
                previewImage(fileInput);
            }
        }
    });
</script>
