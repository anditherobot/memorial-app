<?php $__env->startSection('content'); ?>
  <div class="max-w-2xl mx-auto space-y-6">
    <?php if(session('status')): ?>
      <div class="p-3 rounded bg-green-50 text-green-700"><?php echo e(session('status')); ?></div>
    <?php endif; ?>

    <h1 class="text-xl font-semibold">Share a Wish</h1>
    <form method="POST" action="<?php echo e(route('wishes.store')); ?>" class="space-y-3"
          hx-post="<?php echo e(route('wishes.store')); ?>"
          hx-target="#wish-status"
          hx-swap="innerHTML">
      <?php echo csrf_field(); ?>
      <div class="hidden">
        <label>Website <input type="text" name="website" value=""></label>
      </div>
      <div>
        <label class="block text-sm mb-1">Your name</label>
        <input name="name" value="<?php echo e(old('name')); ?>" required maxlength="120" class="w-full border rounded px-3 py-2" />
        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-sm"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>
      <div>
        <label class="block text-sm mb-1">Message</label>
        <textarea name="message" rows="4" required maxlength="2000" class="w-full border rounded px-3 py-2"><?php echo e(old('message')); ?></textarea>
        <?php $__errorArgs = ['message'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="text-red-600 text-sm"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
      </div>
      <button class="px-4 py-2 bg-gray-900 text-white rounded">Submit</button>
    </form>

    <div id="wish-status" class="text-sm text-gray-700"></div>

    <h2 class="text-lg font-semibold">Recent Wishes</h2>
    <div class="space-y-4">
      <?php $__empty_1 = true; $__currentLoopData = $wishes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wish): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <article class="p-4 bg-white border rounded">
          <div class="text-sm text-gray-500"><?php echo e($wish->created_at->diffForHumans()); ?></div>
          <div class="font-medium"><?php echo e($wish->name); ?></div>
          <p class="mt-2"><?php echo e($wish->message); ?></p>
        </article>
      <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="text-gray-500">No wishes yet. Be the first.</div>
      <?php endif; ?>
    </div>

    <div>
      <?php echo e($wishes->links()); ?>

    </div>
  </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\expan\Desktop\memorial\resources\views/wishes/index.blade.php ENDPATH**/ ?>