<?php $__env->startSection('content'); ?>
  <div class="max-w-3xl mx-auto">
    <!-- Header with back link -->
    <div class="mb-6">
      <a href="/" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900 mb-4">
        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
        </svg>
        Back to the obituary
      </a>

      <div class="flex items-center mb-4">
        <svg class="w-8 h-8 mr-3 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
        </svg>
        <h1 class="text-3xl font-bold text-gray-900">Add a photo</h1>
      </div>

      <p class="text-gray-600 text-lg">
        Share your fondest memories of Alex Morgan on this page. You can add pictures and videos to pay tribute to Alex Morgan's life.
      </p>
    </div>

    <?php if(session('status')): ?>
      <div class="mb-6 p-4 bg-green-50 border border-green-200 text-green-800 rounded-md">
        <?php echo e(session('status')); ?>

      </div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <div class="mb-6" x-data="{ activeTab: 'photos' }">
      <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
          <button
            @click="activeTab = 'photos'"
            :class="activeTab === 'photos' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            class="whitespace-nowrap border-b-2 py-2 px-1 text-sm font-medium flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            Photos
          </button>
          <button
            @click="activeTab = 'videos'"
            :class="activeTab === 'videos' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
            class="whitespace-nowrap border-b-2 py-2 px-1 text-sm font-medium flex items-center"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path>
            </svg>
            Videos
          </button>
        </nav>
      </div>

      <!-- Photos Tab Content -->
      <div x-show="activeTab === 'photos'" class="mt-6">
        <form method="POST" action="<?php echo e(route('upload.store')); ?>" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>
          <div
            class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-gray-400 transition-colors"
            x-data="{ dragOver: false }"
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="dragOver = false; handleDrop($event)"
            :class="dragOver ? 'border-blue-400 bg-blue-50' : ''"
          >
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>

            <div class="space-y-2">
              <p class="text-xl text-gray-600">
                <button type="button" class="text-blue-600 hover:text-blue-500 underline" onclick="document.getElementById('photo-file').click()">
                  Choose photos
                </button>
              </p>
              <p class="text-sm text-gray-500">or drag and drop photos here</p>
              <p class="text-xs text-gray-400">PNG, JPG, GIF up to 50MB</p>
            </div>

            <input
              type="file"
              id="photo-file"
              name="file"
              accept="image/*"
              required
              class="hidden"
              @change="if(this.files.length) this.form.submit()"
            />
          </div>

          <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="mt-2 text-red-600 text-sm"><?php echo e($message); ?></div>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </form>
      </div>

      <!-- Videos Tab Content -->
      <div x-show="activeTab === 'videos'" class="mt-6">
        <form method="POST" action="<?php echo e(route('upload.store')); ?>" enctype="multipart/form-data">
          <?php echo csrf_field(); ?>
          <div
            class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center hover:border-gray-400 transition-colors"
            x-data="{ dragOver: false }"
            @dragover.prevent="dragOver = true"
            @dragleave.prevent="dragOver = false"
            @drop.prevent="dragOver = false; handleDrop($event)"
            :class="dragOver ? 'border-blue-400 bg-blue-50' : ''"
          >
            <svg class="mx-auto h-12 w-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
            </svg>

            <div class="space-y-2">
              <p class="text-xl text-gray-600">
                <button type="button" class="text-blue-600 hover:text-blue-500 underline" onclick="document.getElementById('video-file').click()">
                  Choose videos
                </button>
              </p>
              <p class="text-sm text-gray-500">or drag and drop videos here</p>
              <p class="text-xs text-gray-400">MP4, MOV up to 50MB</p>
            </div>

            <input
              type="file"
              id="video-file"
              name="file"
              accept="video/*"
              required
              class="hidden"
              @change="if(this.files.length) this.form.submit()"
            />
          </div>

          <?php $__errorArgs = ['file'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
            <div class="mt-2 text-red-600 text-sm"><?php echo e($message); ?></div>
          <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </form>
      </div>
    </div>
  </div>

  <script>
    function handleDrop(event) {
      const files = event.dataTransfer.files;
      if (files.length > 0) {
        const activeTab = event.target.closest('[x-data]').__x.$data.activeTab;
        const fileInput = activeTab === 'photos' ? document.getElementById('photo-file') : document.getElementById('video-file');

        // Create a new FileList with the dropped files
        fileInput.files = files;

        // Submit the form
        fileInput.form.submit();
      }
    }
  </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\expan\Desktop\memorial\resources\views/upload/index.blade.php ENDPATH**/ ?>