<?php $__env->startSection('title', 'In Loving Memory — Alex Morgan'); ?>

<?php $__env->startSection('content'); ?>
    <!-- Hero Bio Section -->
    <section aria-labelledby="bio-title" class="hero-section rounded-3xl p-8 md:p-12 mb-16 fade-in">
        <div class="relative z-10">
            <div class="text-center mb-8">
                <h1 id="bio-title" class="text-4xl md:text-5xl font-bold elegant-title text-shadow mb-4">Alex Morgan</h1>
                <div class="w-24 h-1 bg-gradient-to-r from-indigo-400 to-purple-400 mx-auto rounded-full mb-6"></div>
                <p class="text-lg text-gray-600 italic">1985 — 2025</p>
            </div>
            <div class="max-w-4xl mx-auto">
                <p class="text-lg md:text-xl text-gray-700 leading-relaxed text-center font-light"><?php echo e($bio); ?></p>
            </div>
        </div>
    </section>

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Photos Section -->
    <section aria-labelledby="photos-title" class="mb-16 fade-in fade-in-delay-1">
        <div class="text-center mb-12">
            <h2 id="photos-title" class="text-3xl md:text-4xl font-bold elegant-title text-shadow mb-4">Cherished Memories</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">A collection of moments that capture Alex's spirit and the joy they brought to our lives.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php $__currentLoopData = $photos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $photo): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <figure class="photo-card rounded-2xl bg-white shadow-lg overflow-hidden fade-in" style="animation-delay: <?php echo e(0.1 * ($index + 2)); ?>s">
                    <div class="aspect-[4/3] overflow-hidden">
                        <img src="<?php echo e($photo['url']); ?>" alt="<?php echo e($photo['alt']); ?>" class="w-full h-full object-cover" />
                    </div>
                    <figcaption class="p-6">
                        <p class="text-gray-700 font-medium text-center"><?php echo e($photo['caption']); ?></p>
                    </figcaption>
                </figure>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>

    <!-- Section Divider -->
    <div class="section-divider"></div>

    <!-- Updates Section -->
    <section aria-labelledby="updates-title" class="mb-12 fade-in fade-in-delay-2">
        <div class="text-center mb-12">
            <h2 id="updates-title" class="text-3xl md:text-4xl font-bold elegant-title text-shadow mb-4">Updates & Events</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">Stay informed about memorial services, gatherings, and ways to honor Alex's memory.</p>
        </div>
        <div class="space-y-6">
            <?php $__currentLoopData = $updates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="update-card rounded-2xl p-6 md:p-8 shadow-lg fade-in" style="animation-delay: <?php echo e(0.1 * ($index + 4)); ?>s">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-4">
                        <div class="flex-1">
                            <h3 class="text-xl font-semibold text-gray-900 mb-2"><?php echo e($item['title']); ?></h3>
                            <time datetime="<?php echo e($item['date']); ?>" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-800">
                                <?php echo e(\Illuminate\Support\Carbon::parse($item['date'])->toFormattedDateString()); ?>

                            </time>
                        </div>
                    </div>

                    <?php if(!empty($item['address'])): ?>
                        <div class="mb-4 p-4 bg-gray-50 rounded-xl">
                            <div class="flex items-start gap-2">
                                <svg class="w-5 h-5 text-gray-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <p class="text-gray-700"><?php echo e($item['address']); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if(!empty($item['notes'])): ?>
                        <p class="text-gray-700 mb-4 leading-relaxed"><?php echo e($item['notes']); ?></p>
                    <?php endif; ?>

                    <?php if(!empty($item['links'])): ?>
                        <div class="flex flex-wrap gap-3">
                            <?php $__currentLoopData = $item['links']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $link): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <a href="<?php echo e($link['url']); ?>"
                                   class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors duration-200 shadow-md hover:shadow-lg"
                                   target="_blank"
                                   rel="noopener">
                                    <?php echo e($link['label']); ?>

                                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                    </svg>
                                </a>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\expan\Desktop\memorial\resources\views/home.blade.php ENDPATH**/ ?>