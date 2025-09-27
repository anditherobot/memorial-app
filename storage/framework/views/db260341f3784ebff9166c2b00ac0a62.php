<?php use Illuminate\Support\Str; ?>
<?php $__currentLoopData = $posts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $post): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
  <?php $cover = $post->media()->with('derivatives')->first(); $thumb = optional($cover?->derivatives->first()); ?>
  <article class="update-card border rounded-lg p-4 flex gap-4">
    <?php if($cover): ?>
      <img src="<?php echo e(Storage::disk('public')->url(($thumb?->storage_path) ?? $cover->storage_path)); ?>" class="w-28 h-28 object-cover rounded" alt="cover" />
    <?php endif; ?>
    <a href="<?php echo e(route('updates.show', $post)); ?>" class="block flex-1">
      <h2 class="text-lg font-semibold"><?php echo e($post->title); ?></h2>
      <div class="mt-1 space-x-2">
        <?php if($post->author_name): ?>
          <span class="chip bg-gray-100"><?php echo e($post->author_name); ?></span>
        <?php endif; ?>
        <?php if($post->published_at): ?>
          <span class="chip bg-gray-100"><?php echo e($post->published_at->format('M j, Y')); ?></span>
        <?php endif; ?>
      </div>
      <div class="mt-2 prose max-w-none"><?php echo Str::limit(strip_tags($post->body), 240); ?></div>
    </a>
  </article>
<?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<?php /**PATH C:\Users\expan\Desktop\memorial\resources\views/updates/_items.blade.php ENDPATH**/ ?>