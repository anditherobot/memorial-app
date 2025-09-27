<?php $__env->startSection('content'); ?>
  <div class="max-w-3xl mx-auto space-y-6">
    <h1 class="text-xl font-semibold">Updates</h1>
    <div id="updates-list" class="space-y-4">
      <?php if($posts->count()): ?>
        <?php echo $__env->make('updates._items', ['posts' => $posts], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
      <?php else: ?>
        <div class="text-gray-500">No updates yet.</div>
      <?php endif; ?>
    </div>
    <?php if($posts->hasMorePages()): ?>
      <button
        id="load-more"
        class="px-4 py-2 bg-gray-900 text-white rounded"
        hx-get="<?php echo e($posts->nextPageUrl()); ?>"
        hx-target="#updates-list"
        hx-swap="beforeend"
        hx-trigger="click"
      >Load more</button>
    <?php endif; ?>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\expan\Desktop\memorial\resources\views/updates/index.blade.php ENDPATH**/ ?>