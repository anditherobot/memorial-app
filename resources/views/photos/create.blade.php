@extends('layouts.app')

@section('title', 'Upload Photos')

@section('breadcrumbs')
    <li>
        <div class="flex items-center">
            <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
            </svg>
            <a href="{{ route('photos.create') }}" class="ml-2 text-sm font-medium text-gray-500 hover:text-gray-700">Upload</a>
        </div>
    </li>
@endsection

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold">Upload Photos</h1>
            <p class="text-sm text-gray-600">Logged in as <strong>{{ auth()->user()->name }}</strong></p>
        </div>

        <div x-data="photoUploader()" class="max-w-3xl mx-auto">
            <div class="bg-white p-8 rounded-lg shadow-md">
                <div class="mb-4">
                    <label for="photos" class="block text-sm font-medium text-gray-700 mb-2">Select up to 30 images (max 12MB each)</label>
                    <input type="file" id="photos" multiple @change="handleFileSelect" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"/>
                </div>

                <button @click="uploadFiles" :disabled="files.length === 0 || uploading" class="w-full bg-blue-600 text-white font-bold py-3 px-4 rounded-lg hover:bg-blue-700 disabled:bg-gray-400 transition-colors">
                    <span x-show="!uploading">Upload</span>
                    <span x-show="uploading">Uploading...</span>
                </button>
            </div>

            <div x-show="files.length > 0" class="mt-8">
                <h2 class="text-xl font-semibold mb-4">Uploads</h2>
                <div class="space-y-4">
                    <template x-for="(file, index) in files" :key="index">
                        <div class="bg-white p-4 rounded-lg shadow-md flex items-center justify-between">
                            <div class="flex items-center">
                                <img :src="file.preview" class="w-16 h-16 object-cover rounded-lg mr-4"/>
                                <div>
                                    <p class="font-semibold text-gray-800" x-text="file.name"></p>
                                    <p class="text-sm text-gray-500" x-text="file.status"></p>
                                    <p x-show="file.error" class="text-sm text-red-500" x-text="file.error"></p>
                                </div>
                            </div>
                            <div x-show="file.status === 'Ready'">
                                <img :src="file.thumb_url" class="w-16 h-16 object-cover rounded-lg"/>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <script>
        function photoUploader() {
            return {
                files: [],
                uploading: false,
                handleFileSelect(event) {
                    this.files = Array.from(event.target.files).map(file => ({
                        file: file,
                        name: file.name,
                        preview: URL.createObjectURL(file),
                        status: 'Waiting',
                        error: null,
                        thumb_url: null
                    }));
                },
                async uploadFiles() {
                    this.uploading = true;
                    for (let i = 0; i < this.files.length; i++) {
                        const file = this.files[i];
                        file.status = 'Uploading';
                        const formData = new FormData();
                        formData.append('images[]', file.file);

                        try {
                            const response = await fetch('{{ route("photos.store") }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                                },
                                body: formData
                            });

                            if (!response.ok) {
                                throw new Error('Upload failed');
                            }

                            const data = await response.json();
                            file.status = 'Processing';
                            this.pollStatus(file, data.uuids[0]);
                        } catch (error) {
                            file.status = 'Error';
                            file.error = error.message;
                        }
                    }
                    this.uploading = false;
                },
                pollStatus(file, uuid) {
                    const interval = setInterval(async () => {
                        try {
                            const response = await fetch(`/photos/${uuid}/status`);
                            const data = await response.json();

                            if (data.status === 'ready') {
                                file.status = 'Ready';
                                file.thumb_url = data.thumb_url;
                                clearInterval(interval);
                            } else if (data.status === 'error') {
                                file.status = 'Error';
                                file.error = data.error;
                                clearInterval(interval);
                            }
                        } catch (error) {
                            file.status = 'Error';
                            file.error = 'Failed to get status';
                            clearInterval(interval);
                        }
                    }, 2000);
                }
            }
        }
    </script>
@endsection
