<?php $__env->startSection('content'); ?>
  <div class="max-w-5xl mx-auto space-y-6">
    <h1 class="text-2xl font-semibold">Admin Dashboard</h1>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
      <div class="p-4 bg-white border rounded">
        <div class="text-sm text-gray-500">Pending Wishes</div>
        <div class="text-2xl font-semibold"><?php echo e($stats['wishes_pending']); ?></div>
      </div>
      <div class="p-4 bg-white border rounded">
        <div class="text-sm text-gray-500">Total Wishes</div>
        <div class="text-2xl font-semibold"><?php echo e($stats['wishes_total']); ?></div>
      </div>
      <div class="p-4 bg-white border rounded">
        <div class="text-sm text-gray-500">Posts</div>
        <div class="text-2xl font-semibold"><?php echo e($stats['posts_total']); ?></div>
      </div>
      <div class="p-4 bg-white border rounded">
        <div class="text-sm text-gray-500">Media</div>
        <div class="text-2xl font-semibold"><?php echo e($stats['media_total']); ?></div>
      </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
      <a href="<?php echo e(route('admin.wishes')); ?>" class="p-4 rounded border bg-white hover:bg-gray-50">Moderate Wishes</a>
      <a href="<?php echo e(route('gallery.index')); ?>" class="p-4 rounded border bg-white hover:bg-gray-50">View Gallery</a>
      <a href="<?php echo e(route('updates.index')); ?>" class="p-4 rounded border bg-white hover:bg-gray-50">View Updates</a>
      <a href="<?php echo e(route('admin.gallery')); ?>" class="p-4 rounded border bg-white hover:bg-gray-50">Manage Gallery</a>
      <a href="<?php echo e(route('admin.updates.index')); ?>" class="p-4 rounded border bg-white hover:bg-gray-50">Manage Updates</a>
      <a href="<?php echo e(route('admin.tasks.index')); ?>" class="p-4 rounded border bg-blue-50 hover:bg-blue-100 text-blue-700 font-medium">🎯 Task Tracker</a>
      <a href="<?php echo e(route('admin.docs')); ?>" class="p-4 rounded border bg-green-50 hover:bg-green-100 text-green-700 font-medium">📚 Documentation</a>
    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\expan\Desktop\memorial\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>