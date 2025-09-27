<?php $__env->startSection('content'); ?>
  <div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-semibold mb-4">Gallery</h1>

    <?php if($images->count() > 0): ?>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4" id="gallery-grid">
        <?php $__currentLoopData = $images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <?php $thumb = optional($img->derivatives()->where('type','thumbnail')->first()); ?>
          <div class="task-card-ui border rounded-lg overflow-hidden bg-white">
            <a href="<?php echo e(\Illuminate\Support\Facades\Storage::disk('public')->url($img->storage_path)); ?>" class="glightbox" data-gallery="memorial">
              <?php if($thumb): ?>
                <img src="<?php echo e(\Illuminate\Support\Facades\Storage::disk('public')->url($thumb->storage_path)); ?>" alt="<?php echo e($img->original_filename); ?>" class="w-full h-44 object-cover" />
              <?php else: ?>
                <img src="<?php echo e(\Illuminate\Support\Facades\Storage::disk('public')->url($img->storage_path)); ?>" alt="<?php echo e($img->original_filename); ?>" class="w-full h-44 object-cover" />
              <?php endif; ?>
            </a>
            <div class="p-2 text-xs flex items-center justify-between">
              <span class="truncate text-gray-700"><?php echo e($img->original_filename); ?></span>
              <?php if($img->width && $img->height): ?>
                <span class="chip bg-gray-100"><?php echo e($img->width); ?>×<?php echo e($img->height); ?></span>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
      <div class="mt-4"><?php echo e($images->links()); ?></div>
    <?php else: ?>
      <p class="text-gray-600 mb-3">Sample gallery (no uploads yet).</p>
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4" id="gallery-grid">
        <?php $__currentLoopData = ($samples ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
          <div class="task-card-ui border rounded-lg overflow-hidden bg-white">
            <a href="<?php echo e(asset($path)); ?>" class="glightbox" data-gallery="memorial" aria-label="Open sample image <?php echo e($i+1); ?>">
              <img src="<?php echo e(asset($path)); ?>" alt="Sample image <?php echo e($i+1); ?>" class="w-full h-44 object-cover bg-white" />
            </a>
            <div class="p-2 text-xs flex items-center justify-between">
              <span class="truncate text-gray-700">Sample <?php echo e($i+1); ?></span>
              <span class="chip bg-gray-100">SVG</span>
            </div>
          </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
      </div>
    <?php endif; ?>
  </div>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      if (typeof GLightbox !== 'undefined') {
        GLightbox({ selector: '.glightbox' });
      }
    });
  </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\expan\Desktop\memorial\resources\views/gallery/index.blade.php ENDPATH**/ ?>