@props([
    'action',
    'title' => 'Upload Files',
    'inputName' => 'photos',
    'acceptedFileTypes' => 'image/jpeg,image/jpg,image/png,image/gif,image/webp,image/heic',
    'fileTypesDescription' => 'PNG, JPG, GIF, WEBP, HEIC up to 10MB each',
    'maxFileSize' => 10485760, // 10MB in bytes
])

<div class="p-6 bg-white border rounded-lg shadow-sm space-y-4">
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="uploadForm-{{ $inputName }}">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $title }}</label>

            <!-- File Input -->
            <input id="{{ $inputName }}"
                   name="{{ $inputName }}[]"
                   type="file"
                   accept="{{ $acceptedFileTypes }}"
                   class="sr-only"
                   multiple
                   aria-label="Upload images"
                   data-input-name="{{ $inputName }}" />

            <!-- Drop Zone -->
            <label for="{{ $inputName }}"
                   class="block cursor-pointer border-2 border-dashed border-gray-300 rounded-md p-6 hover:border-gray-400 transition-colors"
                   id="dropZone-{{ $inputName }}"
                   data-input-name="{{ $inputName }}">
                <div class="space-y-1 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    <div class="flex text-sm text-gray-600 justify-center">
                        <span class="relative font-medium text-indigo-600 hover:text-indigo-500">Upload files</span>
                        <p class="pl-1">or drag and drop</p>
                    </div>
                    <p class="text-xs text-gray-500">{{ $fileTypesDescription }}</p>
                </div>
            </label>

            <!-- Preview Area -->
            <div id="previewArea-{{ $inputName }}" class="mt-4 hidden">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-medium text-gray-700">Selected files: <span id="fileCountText-{{ $inputName }}">0</span></p>
                    <button type="button" onclick="window.uploadHandler_{{ $inputName }}.clearFiles()" class="text-sm text-red-600 hover:text-red-800">Clear all</button>
                </div>
                <div id="previewGrid-{{ $inputName }}" class="grid grid-cols-3 gap-2 max-h-64 overflow-y-auto"></div>
            </div>

            <!-- Upload Button -->
            <div class="mt-4 hidden" id="uploadActions-{{ $inputName }}">
                <button type="submit"
                        class="w-full bg-indigo-600 text-white py-2 px-4 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                        id="uploadButton-{{ $inputName }}">
                    <span id="uploadButtonText-{{ $inputName }}">Upload <span id="fileCount-{{ $inputName }}">0</span> file(s)</span>
                    <span id="uploadSpinner-{{ $inputName }}" class="hidden inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        Uploading...
                    </span>
                </button>
            </div>

            <!-- Progress Bar -->
            <div id="progressBar-{{ $inputName }}" class="mt-4 hidden">
                <div class="w-full bg-gray-200 rounded-full h-2.5">
                    <div id="progressBarFill-{{ $inputName }}" class="bg-indigo-600 h-2.5 rounded-full transition-all duration-300" style="width: 0%"></div>
                </div>
                <p class="text-sm text-gray-600 mt-1 text-center" id="progressText-{{ $inputName }}">0%</p>
            </div>

            @error($inputName)
                <div class="text-red-600 text-sm mt-2 p-2 bg-red-50 rounded">{{ $message }}</div>
            @enderror
        </div>

        {{ $slot }}
    </form>
</div>

<script>
(function() {
    const inputName = '{{ $inputName }}';
    const maxFileSize = {{ $maxFileSize }};
    const allowedTypes = '{{ $acceptedFileTypes }}'.split(',');

    class UploadHandler {
        constructor() {
            this.selectedFiles = [];
            this.init();
        }

        init() {
            const fileInput = document.getElementById(inputName);
            const dropZone = document.getElementById(`dropZone-${inputName}`);
            const form = document.getElementById(`uploadForm-${inputName}`);

            if (!fileInput || !dropZone || !form) return;

            fileInput.addEventListener('change', (e) => this.handleFileSelection(e));
            form.addEventListener('submit', (e) => this.handleSubmit(e));

            // Drag and drop
            dropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.add('border-indigo-500', 'bg-indigo-50');
            });

            dropZone.addEventListener('dragleave', (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');
            });

            dropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                e.stopPropagation();
                dropZone.classList.remove('border-indigo-500', 'bg-indigo-50');

                const files = Array.from(e.dataTransfer.files);
                this.addFiles(files);
            });
        }

        handleFileSelection(event) {
            const files = Array.from(event.target.files);
            this.addFiles(files);
        }

        addFiles(files) {
            const validFiles = files.filter(file => {
                // Check file size
                if (file.size > maxFileSize) {
                    alert(`File "${file.name}" is too large. Maximum size is ${Math.round(maxFileSize / 1024 / 1024)}MB.`);
                    return false;
                }

                // Check file type
                if (!allowedTypes.some(type => file.type.match(type.replace('*', '.*')))) {
                    alert(`File "${file.name}" is not a supported image type.`);
                    return false;
                }

                return true;
            });

            this.selectedFiles = [...this.selectedFiles, ...validFiles];
            this.updatePreview();
        }

        updatePreview() {
            const previewArea = document.getElementById(`previewArea-${inputName}`);
            const previewGrid = document.getElementById(`previewGrid-${inputName}`);
            const uploadActions = document.getElementById(`uploadActions-${inputName}`);
            const fileCount = document.getElementById(`fileCount-${inputName}`);
            const fileCountText = document.getElementById(`fileCountText-${inputName}`);

            if (this.selectedFiles.length === 0) {
                previewArea.classList.add('hidden');
                uploadActions.classList.add('hidden');
                return;
            }

            previewArea.classList.remove('hidden');
            uploadActions.classList.remove('hidden');
            fileCount.textContent = this.selectedFiles.length;
            fileCountText.textContent = this.selectedFiles.length;

            previewGrid.innerHTML = '';
            this.selectedFiles.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const div = document.createElement('div');
                    div.className = 'relative group';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="w-full h-24 object-cover rounded border" alt="${file.name}">
                        <button type="button"
                                onclick="window.uploadHandler_${inputName}.removeFile(${index})"
                                class="absolute top-1 right-1 bg-red-500 text-white rounded-full p-1 opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
                                aria-label="Remove ${file.name}">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                        <p class="text-xs text-gray-600 mt-1 truncate" title="${file.name}">${file.name}</p>
                        <p class="text-xs text-gray-500">${(file.size / 1024).toFixed(1)} KB</p>
                    `;
                    previewGrid.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        }

        removeFile(index) {
            this.selectedFiles.splice(index, 1);
            this.updatePreview();
        }

        clearFiles() {
            this.selectedFiles = [];
            document.getElementById(inputName).value = '';
            this.updatePreview();
        }

        async handleSubmit(e) {
            e.preventDefault();

            if (this.selectedFiles.length === 0) {
                alert('Please select at least one file to upload.');
                return;
            }

            const formData = new FormData();
            const form = e.target;
            formData.append('_token', form.querySelector('input[name="_token"]').value);

            this.selectedFiles.forEach(file => {
                formData.append(`${inputName}[]`, file);
            });

            const uploadButton = document.getElementById(`uploadButton-${inputName}`);
            const uploadButtonText = document.getElementById(`uploadButtonText-${inputName}`);
            const uploadSpinner = document.getElementById(`uploadSpinner-${inputName}`);
            const progressBar = document.getElementById(`progressBar-${inputName}`);
            const progressBarFill = document.getElementById(`progressBarFill-${inputName}`);
            const progressText = document.getElementById(`progressText-${inputName}`);

            uploadButton.disabled = true;
            uploadButtonText.classList.add('hidden');
            uploadSpinner.classList.remove('hidden');
            progressBar.classList.remove('hidden');

            try {
                const xhr = new XMLHttpRequest();

                xhr.upload.addEventListener('progress', (e) => {
                    if (e.lengthComputable) {
                        const percentComplete = (e.loaded / e.total) * 100;
                        progressBarFill.style.width = percentComplete + '%';
                        progressText.textContent = Math.round(percentComplete) + '%';
                    }
                });

                xhr.addEventListener('load', () => {
                    if (xhr.status === 200 || xhr.status === 302) {
                        progressText.textContent = 'Upload complete! Reloading...';
                        setTimeout(() => window.location.reload(), 500);
                    } else {
                        alert('Upload failed. Please try again.');
                        this.resetUploadUI();
                    }
                });

                xhr.addEventListener('error', () => {
                    alert('Upload failed. Please check your connection.');
                    this.resetUploadUI();
                });

                xhr.open('POST', form.action);
                xhr.send(formData);
            } catch (error) {
                console.error('Upload error:', error);
                alert('Upload failed. Please try again.');
                this.resetUploadUI();
            }
        }

        resetUploadUI() {
            const uploadButton = document.getElementById(`uploadButton-${inputName}`);
            const uploadButtonText = document.getElementById(`uploadButtonText-${inputName}`);
            const uploadSpinner = document.getElementById(`uploadSpinner-${inputName}`);
            const progressBar = document.getElementById(`progressBar-${inputName}`);
            const progressBarFill = document.getElementById(`progressBarFill-${inputName}`);

            uploadButton.disabled = false;
            uploadButtonText.classList.remove('hidden');
            uploadSpinner.classList.add('hidden');
            progressBar.classList.add('hidden');
            progressBarFill.style.width = '0%';
        }
    }

    // Initialize and expose to global scope for button onclick handlers
    window[`uploadHandler_${inputName}`] = new UploadHandler();
})();
</script>