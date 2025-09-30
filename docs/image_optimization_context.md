
# Image Optimization Context

This document contains the collected source code and configuration files related to the image optimization workflow in the Salem 2.5 application.

## Files Included

*   `app/Jobs/OptimizeAlbumImages.php`
*   `app/Jobs/OptimizeJumbotronImage.php`
*   `app/Services/FileUploadService.php`
*   `app/Http/Controllers/Admin/ImageController.php`
*   `app/Http/Controllers/Admin/JumbotronController.php`
*   `app/Http/Controllers/Admin/AlbumController.php`
*   `app/Http/Controllers/Admin/PictureCardController.php`
*   `routes/web.php`
*   `database/migrations/2025_07_18_144150_add_size_columns_to_images_table.php`
*   `database/migrations/2025_09_26_000601_add_optimization_fields_to_jumbotron_settings.php`
*   `database/migrations/2025_09_26_000945_add_horizontal_optimization_fields_to_jumbotron_settings.php`

---

## `app/Jobs/OptimizeAlbumImages.php`

```php
<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class OptimizeAlbumImages implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The image instance.
     *
     * @var \App\Models\Image
     */
    public $image;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Models\Image $image)
    {
        $this->image = $image;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $optimizerChain = OptimizerChainFactory::create();

            // Get the original file's full path from where it was initially uploaded
            $originalFileFullPath = storage_path('app/'.$this->image->file_path);
            $originalSize = filesize($originalFileFullPath);

            // Define paths for the permanent original and the final optimized image
            $permanentOriginalPath = 'album_originals/'.$this->image->album_id.'/'.basename($this->image->file_path);
            $finalOptimizedPath = 'public/albums/'.$this->image->album_id.'/'.basename($this->image->file_path);

            // Ensure directories exist with proper permissions
            $originalDir = dirname($permanentOriginalPath);
            $optimizedDir = dirname($finalOptimizedPath);
            
            Storage::makeDirectory($originalDir);
            Storage::makeDirectory($optimizedDir);
            
            // Fix directory permissions if needed
            $originalDirPath = storage_path('app/' . $originalDir);
            $optimizedDirPath = storage_path('app/' . $optimizedDir);
            
            if (!is_writable($originalDirPath)) {
                \Log::warning('Original directory not writable, fixing permissions', [
                    'directory' => $originalDirPath
                ]);
                chmod($originalDirPath, 0755);
            }
            
            if (!is_writable($optimizedDirPath)) {
                \Log::warning('Optimized directory not writable, fixing permissions', [
                    'directory' => $optimizedDirPath
                ]);
                chmod($optimizedDirPath, 0755);
            }

            // Move the original uploaded file to its permanent original storage location
            Storage::move($this->image->file_path, $permanentOriginalPath);

            // Copy the original file to the public path for optimization (this will be overwritten by optimized version)
            Storage::copy($permanentOriginalPath, $finalOptimizedPath);

            // Optimize the image in its public path (in-place optimization)
            if (function_exists('proc_open')) {
                $optimizerChain->optimize(storage_path('app/'.$finalOptimizedPath));
                \Log::debug('Spatie optimization completed in job', ['image_id' => $this->image->id]);
            } else {
                \Log::warning('proc_open not available in job, skipping Spatie optimization', [
                    'image_id' => $this->image->id
                ]);
            }

            $optimizedSize = Storage::size($finalOptimizedPath);

            $this->image->update([
                'path_original' => $permanentOriginalPath,
                'original_size' => $originalSize,
                'optimized_size' => $optimizedSize,
                'file_path' => $finalOptimizedPath,
            ]);
            \Log::debug('OptimizeAlbumImages: Image model updated', ['image_after_update' => $this->image->fresh()]);
        } catch (\Exception $e) {
            \Log::error('Image optimization job failed', [
                'image_id' => $this->image->id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'original_file_path' => $originalFileFullPath ?? 'N/A',
                'permanent_original_path' => $permanentOriginalPath ?? 'N/A',
                'final_optimized_path' => $finalOptimizedPath ?? 'N/A'
            ]);
            
            // Re-throw the exception so it can be caught by the controller
            throw $e;
        }
    }
}
```

---

## `app/Jobs/OptimizeJumbotronImage.php`

```php
<?php

namespace App\Jobs;

use App\Models\JumbotronSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class OptimizeJumbotronImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public JumbotronSetting $jumbotron;
    public string $context = 'jumbotron'; // 'jumbotron' or 'horizontal'

    public function __construct(JumbotronSetting $jumbotron, string $context = 'jumbotron')
    {
        $this->jumbotron = $jumbotron;
        $this->context = $context;
    }

    public function handle(): void
    {
        try {
            $isHorizontal = $this->context === 'horizontal';
            $relativePath = $isHorizontal ? $this->jumbotron->horizontal_card_image_path : $this->jumbotron->image_path;
            if (!$relativePath) {
                \Log::warning('OptimizeJumbotronImage: No image path set for context', ['context' => $this->context]);
                return;
            }

            // Use explicit disks to avoid path confusion
            $publicDisk = Storage::disk('public'); // storage/app/public
            $localDisk = Storage::disk('local');   // storage/app/private

            $optimizerChain = OptimizerChainFactory::create();

            // Paths
            $publicRelativePath = $relativePath; // e.g., horizontal-cards/filename.jpg
            $publicAbsolutePath = $publicDisk->path($publicRelativePath);

            if (!file_exists($publicAbsolutePath) || !$publicDisk->exists($publicRelativePath)) {
                \Log::error('OptimizeJumbotronImage: File not found', [
                    'absolute_path' => $publicAbsolutePath,
                    'disk' => 'public',
                    'relative_path' => $publicRelativePath,
                    'context' => $this->context,
                ]);
                return;
            }

            $originalSize = filesize($publicAbsolutePath);

            // Permanent originals live on the private local disk
            $originalsDir = $isHorizontal ? 'horizontal_card_originals' : 'jumbotron_originals';
            $permanentOriginalPath = $originalsDir . '/' . basename($relativePath);
            $localDisk->makeDirectory($originalsDir);

            // Persist the original if not already present
            if (!$localDisk->exists($permanentOriginalPath)) {
                $localDisk->put($permanentOriginalPath, $publicDisk->get($publicRelativePath));
            }

            // Ensure directories are writable (best-effort)
            $originalDirPath = storage_path('app/private/' . $originalsDir);
            $optimizedDirPath = dirname($publicAbsolutePath);
            if (is_dir($originalDirPath) && !is_writable($originalDirPath)) {
                @chmod($originalDirPath, 0755);
            }
            if (is_dir($optimizedDirPath) && !is_writable($optimizedDirPath)) {
                @chmod($optimizedDirPath, 0755);
            }

            // Optimize the public file in place
            if (function_exists('proc_open')) {
                $optimizerChain->optimize($publicAbsolutePath);
                \Log::debug('OptimizeJumbotronImage: Spatie optimization completed');
            } else {
                \Log::warning('OptimizeJumbotronImage: proc_open not available, skipping Spatie optimization');
            }

            $optimizedSize = $publicDisk->size($publicRelativePath);

            // Build updates guarded by actual schema to avoid hard failures when migrations lag
            if ($isHorizontal) {
                $candidateUpdates = [
                    'horizontal_image_original_path' => $permanentOriginalPath,
                    'horizontal_image_original_size' => $originalSize,
                    'horizontal_image_optimized_size' => $optimizedSize,
                    'horizontal_image_optimized_at' => now(),
                ];
                $available = array_filter(array_keys($candidateUpdates), fn ($c) => Schema::hasColumn('jumbotron_settings', $c));
                $updates = array_intersect_key($candidateUpdates, array_flip($available));
                if (count($updates) !== count($candidateUpdates)) {
                    \Log::warning('OptimizeJumbotronImage: Some horizontal optimization columns missing. Run migrations.', [
                        'missing_columns' => array_values(array_diff(array_keys($candidateUpdates), $available))
                    ]);
                }
                if (!empty($updates)) {
                    $this->jumbotron->update($updates);
                }
            } else {
                $candidateUpdates = [
                    'image_original_path' => $permanentOriginalPath,
                    'image_original_size' => $originalSize,
                    'image_optimized_size' => $optimizedSize,
                    'image_optimized_at' => now(),
                ];
                $available = array_filter(array_keys($candidateUpdates), fn ($c) => Schema::hasColumn('jumbotron_settings', $c));
                $updates = array_intersect_key($candidateUpdates, array_flip($available));
                if (count($updates) !== count($candidateUpdates)) {
                    \Log::warning('OptimizeJumbotronImage: Some jumbotron optimization columns missing. Run migrations.', [
                        'missing_columns' => array_values(array_diff(array_keys($candidateUpdates), $available))
                    ]);
                }
                if (!empty($updates)) {
                    $this->jumbotron->update($updates);
                }
            }

            \Log::debug('OptimizeJumbotronImage: JumbotronSetting updated', [
                'context' => $this->context,
                'jumbotron' => $this->jumbotron->fresh()
            ]);
        } catch (\Exception $e) {
            \Log::error('OptimizeJumbotronImage: Optimization failed', [
                'context' => $this->context,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
```

---

## `app/Services/FileUploadService.php`

```php
<?php

namespace App\Services;

use App\Models\FileUpload;
use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class FileUploadService
{
    public function uploadAndStore(UploadedFile $file, ?string $directory = 'uploads', ?int $albumId = null, bool $forceOptimize = false): \Illuminate\Database\Eloquent\Model
    {
        $path = $file->store($directory, 'public');

        // Auto-optimize image if it exceeds threshold or force optimization is enabled
        if ($this->shouldOptimize($file, $forceOptimize ? 0 : 1048576)) {
            $fullPath = storage_path('app/public/'.$path);
            $this->optimizeImage($fullPath);
        }

        if ($albumId) {
            return Image::create([
                'album_id' => $albumId,
                'file_path' => $path,
                'path_original' => $path, // Initially, original path is the same
                'original_size' => $file->getSize(),
                'caption' => $file->getClientOriginalName(), // Use original name as default caption
                'order' => 0, // Default order
            ]);
        } else {
            return FileUpload::create([
                'original_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
            ]);
        }
    }

    public function listFiles()
    {
        return FileUpload::latest()->get();
    }

    public function getFileById(int $id)
    {
        return FileUpload::findOrFail($id);
    }

    private function shouldOptimize(UploadedFile $file, int $thresholdBytes = 1048576): bool
    {
        $imageTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/svg+xml'];

        return in_array($file->getClientMimeType(), $imageTypes) &&
               $file->getSize() > $thresholdBytes;
    }

    private function optimizeImage(string $filePath): void
    {
        try {
            $originalSize = filesize($filePath);

            $optimizerChain = OptimizerChainFactory::create();
            $optimizerChain->optimize($filePath);

            $optimizedSize = filesize($filePath);
            $savedBytes = $originalSize - $optimizedSize;
            $percentSaved = round(($savedBytes / $originalSize) * 100, 2);

            \Log::info('Image optimized', [
                'file_path' => $filePath,
                'original_size' => $originalSize,
                'optimized_size' => $optimizedSize,
                'saved_bytes' => $savedBytes,
                'percent_saved' => $percentSaved,
            ]);
        } catch (\Exception $e) {
            \Log::warning('Image optimization failed', [
                'file_path' => $filePath,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
```

---

## `app/Http/Controllers/Admin/ImageController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\OptimizeAlbumImages;
use App\Models\Image;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Image $image)
    {
        Storage::delete($image->file_path);
        $image->delete();

        return back()->with('success', 'Image deleted successfully.');
    }

    /**
     * Optimize an existing image.
     */
    public function optimize(Image $image)
    {
        try {
            \Log::debug('Image optimization started', [
                'image_id' => $image->id,
                'file_path' => $image->file_path,
                'path_original' => $image->path_original
            ]);

            // Check if image is already optimized (has original path different from file path)
            if ($image->path_original && $image->path_original !== $image->file_path) {
                \Log::info('Image already optimized', ['image_id' => $image->id]);
                return back()->with('info', 'Image is already optimized.');
            }

            // Check if file exists before optimization
            $imagePath = storage_path('app/'.$image->file_path);
            if (!file_exists($imagePath)) {
                \Log::error('Image file not found', [
                    'image_id' => $image->id,
                    'expected_path' => $imagePath
                ]);
                return back()->with('error', 'Image file not found at: ' . $imagePath);
            }

            // Check file permissions and fix if needed
            if (!is_readable($imagePath)) {
                \Log::warning('Image file not readable, attempting to fix permissions', [
                    'image_id' => $image->id,
                    'file_path' => $imagePath
                ]);
                chmod($imagePath, 0644);
                
                if (!is_readable($imagePath)) {
                    \Log::error('Cannot read image file after permission fix', [
                        'image_id' => $image->id,
                        'file_path' => $imagePath
                    ]);
                    return back()->with('error', 'Image file is not readable. Please check file permissions.');
                }
            }

            if (!is_writable($imagePath)) {
                \Log::warning('Image file not writable, attempting to fix permissions', [
                    'image_id' => $image->id,
                    'file_path' => $imagePath
                ]);
                chmod($imagePath, 0644);
                
                if (!is_writable($imagePath)) {
                    \Log::error('Cannot write to image file after permission fix', [
                        'image_id' => $image->id,
                        'file_path' => $imagePath
                    ]);
                    return back()->with('error', 'Image file is not writable. Please check file permissions.');
                }
            }

            // Check and fix directory permissions
            $imageDir = dirname($imagePath);
            if (!is_writable($imageDir)) {
                \Log::warning('Image directory not writable, attempting to fix permissions', [
                    'image_id' => $image->id,
                    'directory' => $imageDir
                ]);
                chmod($imageDir, 0755);
                
                if (!is_writable($imageDir)) {
                    \Log::error('Cannot write to image directory after permission fix', [
                        'image_id' => $image->id,
                        'directory' => $imageDir
                    ]);
                    return back()->with('error', 'Image directory is not writable: ' . $imageDir);
                }
            }

            // Check if proc_open is available before running optimization
            if (!function_exists('proc_open')) {
                \Log::warning('proc_open not available, cannot run Spatie optimization', [
                    'image_id' => $image->id
                ]);
                return back()->with('error', 'Image optimization requires proc_open function to be enabled in PHP. Please contact server administrator.');
            }

            // Run the optimization job
            (new OptimizeAlbumImages($image))->handle();

            \Log::debug('Image optimization completed', ['image_id' => $image->id]);
            return back()->with('success', 'Image optimized successfully.');
        } catch (\Exception $e) {
            \Log::error('Image optimization failed', [
                'image_id' => $image->id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'file_path' => $image->file_path ?? 'N/A'
            ]);

            return back()->with('error', 'Failed to optimize image. Error: ' . $e->getMessage());
        }
    }
}
```

---

## `app/Http/Controllers/Admin/JumbotronController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JumbotronSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Jobs\OptimizeJumbotronImage;
use App\Support\Format;

class JumbotronController extends Controller
{
    public function index()
    {
        \Log::debug('Jumbotron admin page accessed');
        $jumbotronSetting = JumbotronSetting::getCurrent();

        return view('admin.jumbotron.index', compact('jumbotronSetting'));
    }

    public function store(Request $request)
    {
        \Log::debug('Jumbotron update request received', ['request_data' => $request->except(['image', 'horizontal_card_image'])]);

        $request->validate([
            'image' => 'nullable|image|max:10240', // 10MB max
            'is_visible' => 'boolean',
            'horizontal_card_image' => 'nullable|image|max:10240',
            'horizontal_card_title' => 'nullable|string|max:255',
            'horizontal_card_is_visible' => 'boolean',
        ]);

        \Log::debug('Jumbotron validation passed');

        $jumbotronSetting = JumbotronSetting::getCurrent();

        // Handle jumbotron image upload
        if ($request->hasFile('image')) {
            \Log::debug('Image upload processing', ['file_name' => $request->file('image')->getClientOriginalName()]);

            // Delete old image if exists
            if ($jumbotronSetting->image_path && Storage::exists('public/'.$jumbotronSetting->image_path)) {
                Storage::delete('public/'.$jumbotronSetting->image_path);
                \Log::debug('Old image deleted');
            }

            // Store new image
            $imagePath = $request->file('image')->store('jumbotron', 'public');
            $jumbotronSetting->image_path = $imagePath;

            // Reset optimization metadata on new upload
            $jumbotronSetting->image_original_path = null;
            $jumbotronSetting->image_original_size = null;
            $jumbotronSetting->image_optimized_size = null;
            $jumbotronSetting->image_optimized_at = null;
            \Log::debug('New image stored', ['path' => $imagePath]);
        }

        // Handle horizontal card image upload
        if ($request->hasFile('horizontal_card_image')) {
            \Log::debug('Horizontal card image upload processing');

            // Delete old horizontal card image if exists
            if ($jumbotronSetting->horizontal_card_image_path && Storage::exists('public/'.$jumbotronSetting->horizontal_card_image_path)) {
                Storage::delete('public/'.$jumbotronSetting->horizontal_card_image_path);
            }

            // Store new horizontal card image
            $horizontalImagePath = $request->file('horizontal_card_image')->store('horizontal-cards', 'public');
            $jumbotronSetting->horizontal_card_image_path = $horizontalImagePath;

            // Reset horizontal optimization metadata on new upload
            $jumbotronSetting->horizontal_image_original_path = null;
            $jumbotronSetting->horizontal_image_original_size = null;
            $jumbotronSetting->horizontal_image_optimized_size = null;
            $jumbotronSetting->horizontal_image_optimized_at = null;
        }

        $jumbotronSetting->is_visible = $request->boolean('is_visible');
        $jumbotronSetting->horizontal_card_title = $request->horizontal_card_title;
        $jumbotronSetting->horizontal_card_is_visible = $request->boolean('horizontal_card_is_visible');

        if (! $jumbotronSetting->exists) {
            $jumbotronSetting->save();
            \Log::debug('Jumbotron setting created');
        } else {
            $jumbotronSetting->save();
            \Log::debug('Jumbotron setting updated');
        }

        return redirect()->route('admin.jumbotron.index')
            ->with('success', 'Homepage settings updated successfully!');
    }

    public function optimize(Request $request)
    {
        \Log::debug('Jumbotron optimize request received');

        $jumbotronSetting = JumbotronSetting::getCurrent();

        if (!$jumbotronSetting->image_path) {
            return redirect()->route('admin.jumbotron.index')->with('error', 'No jumbotron image to optimize.');
        }

        try {
            // Run optimization synchronously so the admin sees immediate status
            OptimizeJumbotronImage::dispatchSync($jumbotronSetting);
            $jumbotronSetting->refresh();
            $orig = (int) ($jumbotronSetting->image_original_size ?? 0);
            $opt = (int) ($jumbotronSetting->image_optimized_size ?? 0);
            $pct = Format::savedPercent($orig, $opt, 0);
            $msg = 'Jumbotron image optimized successfully.';
            if ($orig && $opt) {
                $msg .= " New size: " . Format::humanBytes($opt);
                if (!is_null($pct)) {
                    $msg .= " (saved {$pct}% )";
                }
            }
            return redirect()->route('admin.jumbotron.index')->with('success', $msg);
        } catch (\Exception $e) {
            \Log::error('Jumbotron image optimization failed', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('admin.jumbotron.index')->with('error', 'Failed to optimize jumbotron image: '.$e->getMessage());
        }
    }

    public function optimizeHorizontal(Request $request)
    {
        \Log::debug('Jumbotron optimize horizontal request received');

        $jumbotronSetting = JumbotronSetting::getCurrent();

        if (!$jumbotronSetting->horizontal_card_image_path) {
            return redirect()->route('admin.jumbotron.index')->with('error', 'No horizontal card image to optimize.');
        }

        try {
            OptimizeJumbotronImage::dispatchSync($jumbotronSetting, 'horizontal');
            $jumbotronSetting->refresh();
            $horig = (int) ($jumbotronSetting->horizontal_image_original_size ?? 0);
            $hopt = (int) ($jumbotronSetting->horizontal_image_optimized_size ?? 0);
            $hpct = Format::savedPercent($horig, $hopt, 0);
            $msg = 'Horizontal card image optimized successfully.';
            if ($hopt) {
                $msg .= " New size: " . Format::humanBytes($hopt);
                if (!is_null($hpct)) {
                    $msg .= " (saved {$hpct}% )";
                }
            }
            return redirect()->route('admin.jumbotron.index')->with('success', $msg);
        } catch (\Exception $e) {
            \Log::error('Horizontal card image optimization failed', [
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('admin.jumbotron.index')->with('error', 'Failed to optimize horizontal card image: '.$e->getMessage());
        }
    }
}
```

---

## `app/Http/Controllers/Admin/AlbumController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\OptimizeAlbumImages;
use App\Models\Album;
use App\Services\FileUploadService;
use Illuminate\Http\Request;

class AlbumController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $albums = Album::all();

        return view('admin.albums.index', compact('albums'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.albums.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::debug('Album creation request received.');
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
            'images' => 'array|max:20', // Allow up to 20 images
            'images.*' => 'image|max:15360', // Each image up to 15MB (15 * 1024 KB)
        ]);
        \Log::debug('Album creation validation passed.');

        try {
            \Log::debug('Attempting to create album.', ['request_data' => $request->except('images')]);
            $album = Album::create($request->except('images')); // Exclude images from direct album creation
            \Log::debug('Album created successfully.', ['album_id' => $album->id]);

            if ($request->hasFile('images')) {
                \Log::debug('Files found in request for new album.', ['files_count' => count($request->file('images'))]);
                foreach ($request->file('images') as $imageFile) {
                    \Log::debug('Uploading image for new album.', ['image_name' => $imageFile->getClientOriginalName()]);
                    $imageModel = $this->fileUploadService->uploadAndStore($imageFile, 'albums/'.$album->id, $album->id);
                    // Synchronously optimize the image after upload
                    (new OptimizeAlbumImages($imageModel))->handle();

                    \Log::debug('Image uploaded and optimized for new album.', ['image_name' => $imageFile->getClientOriginalName()]);
                }
            } else {
                \Log::debug('No files found in request for new album.');
            }

            return redirect()->route('admin.albums.index')->with('success', 'Album created successfully.');
        } catch (\Exception $e) {
            \Log::error('Error creating album or uploading images: '.$e->getMessage(), ['exception' => $e]);

            return redirect()->back()->withInput()->with('error', 'Failed to create album or upload images. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Album $album)
    {
        // For now, we'll just redirect to edit or index, or return a simple view
        return redirect()->route('admin.albums.edit', $album);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Album $album)
    {
        return view('admin.albums.edit', compact('album'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Album $album)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_featured' => 'nullable|boolean',
        ]);

        $album->update($request->all());

        return redirect()->route('admin.albums.index')->with('success', 'Album updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Album $album)
    {
        $album->delete();

        return redirect()->route('admin.albums.index')->with('success', 'Album deleted successfully.');
    }

    /**
     * Handle image uploads for a specific album.
     */
    public function uploadImage(Request $request, Album $album)
    {
        $request->validate([
            'images' => 'required|array|max:20', // Allow up to 20 images
            'images.*' => 'image|max:15360', // Each image up to 15MB (15 * 1024 KB)
        ]);

        try {
            foreach ($request->file('images') as $imageFile) {
                $imageModel = $this->fileUploadService->uploadAndStore($imageFile, 'albums/'.$album->id, $album->id);
                // Synchronously optimize the image after upload
                (new OptimizeAlbumImages($imageModel))->handle();
            }

            \Log::debug('Images uploaded and optimized successfully for album '.$album->id);

            return back()->with('success', 'Images uploaded and optimized successfully.');
        } catch (\Exception $e) {
            \Log::error('Error uploading images to album '.$album->id.': '.$e->getMessage());

            return back()->with('error', 'Failed to upload images. Please try again.');
        }
    }
}
```

---

## `app/Http/Controllers/Admin/PictureCardController.php`

```php
<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PictureCard;
use App\Services\FileUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\ImageOptimizer\OptimizerChainFactory;

class PictureCardController extends Controller
{
    protected $fileUploadService;

    public function __construct(FileUploadService $fileUploadService)
    {
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        \Log::debug('PictureCard index requested');
        $pictureCards = PictureCard::orderBy('sort_order')->orderBy('created_at', 'desc')->get();
        
        return view('admin.picture-cards.index', compact('pictureCards'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.picture-cards.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::debug('PictureCard creation started', ['request_data' => $request->except('image')]);
        
        $request->validate([
            'heading' => 'required|string|max:255',
            'image' => 'required|image|max:15360', // 15MB max
            'is_active' => 'nullable|boolean',
        ]);

        \Log::debug('PictureCard validation passed');

        // Auto-assign sort_order based on active count (0, 1, 2 for slots)
        $nextSortOrder = PictureCard::active()->count();
        
        // Upload image using FileUploadService
        $imagePath = null;
        if ($request->hasFile('image')) {
            \Log::debug('Image upload processing', ['file_name' => $request->file('image')->getClientOriginalName()]);
            $uploadedFile = $this->fileUploadService->uploadAndStore($request->file('image'), 'picture-cards');
            $imagePath = $uploadedFile->file_path;
        }

        $pictureCard = PictureCard::create([
            'heading' => $request->heading,
            'image_path' => $imagePath,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $nextSortOrder,
        ]);

        \Log::debug('PictureCard created with auto sort_order', ['picture_card_id' => $pictureCard->id, 'sort_order' => $nextSortOrder]);

        \Log::debug('PictureCard created successfully', ['picture_card_id' => $pictureCard->id]);

        return redirect()->route('admin.picture-cards.index')
            ->with('success', 'Picture card created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PictureCard $pictureCard)
    {
        return view('admin.picture-cards.show', compact('pictureCard'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PictureCard $pictureCard)
    {
        return view('admin.picture-cards.edit', compact('pictureCard'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PictureCard $pictureCard)
    {
        \Log::debug('PictureCard update started', [
            'picture_card_id' => $pictureCard->id,
            'request_data' => $request->except('image')
        ]);

        $request->validate([
            'heading' => 'required|string|max:255',
            'image' => 'nullable|image|max:15360', // 15MB max
            'is_active' => 'nullable|boolean',
        ]);

        \Log::debug('PictureCard update validation passed');

        $updateData = [
            'heading' => $request->heading,
            'is_active' => $request->boolean('is_active', true),
            // Keep existing sort_order on update
        ];

        // Handle new image upload
        if ($request->hasFile('image')) {
            \Log::debug('New image upload processing', ['file_name' => $request->file('image')->getClientOriginalName()]);
            
            // Delete old image if it exists
            if ($pictureCard->image_path) {
                Storage::disk('public')->delete($pictureCard->image_path);
                \Log::debug('Old image deleted', ['old_path' => $pictureCard->image_path]);
            }

            // Upload new image
            $uploadedFile = $this->fileUploadService->uploadAndStore($request->file('image'), 'picture-cards');
            $updateData['image_path'] = $uploadedFile->file_path;
        }

        $pictureCard->update($updateData);

        \Log::debug('PictureCard updated successfully', ['picture_card_id' => $pictureCard->id]);

        return redirect()->route('admin.picture-cards.index')
            ->with('success', 'Picture card updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PictureCard $pictureCard)
    {
        \Log::debug('PictureCard deletion started', ['picture_card_id' => $pictureCard->id]);

        // Delete associated image file
        if ($pictureCard->image_path) {
            Storage::disk('public')->delete($pictureCard->image_path);
            \Log::debug('Image file deleted', ['image_path' => $pictureCard->image_path]);
        }

        $pictureCard->delete();

        \Log::debug('PictureCard deleted successfully', ['picture_card_id' => $pictureCard->id]);

        return redirect()->route('admin.picture-cards.index')
            ->with('success', 'Picture card deleted successfully.');
    }

    /**
     * Optimize image for web use
     */
    public function optimizeImage(PictureCard $pictureCard)
    {
        \Log::debug('Image optimization started', ['picture_card_id' => $pictureCard->id]);

        if (!$pictureCard->image_path) {
            return redirect()->back()->with('error', 'No image to optimize.');
        }

        try {
            $imagePath = storage_path('app/public/' . $pictureCard->image_path);
            
            if (!file_exists($imagePath)) {
                \Log::error('Picture card image file not found', [
                    'picture_card_id' => $pictureCard->id,
                    'expected_path' => $imagePath
                ]);
                return redirect()->back()->with('error', 'Image file not found at: ' . $imagePath);
            }

            // Check file permissions and fix if needed
            if (!is_readable($imagePath)) {
                \Log::warning('Picture card image file not readable, attempting to fix permissions', [
                    'picture_card_id' => $pictureCard->id,
                    'file_path' => $imagePath
                ]);
                chmod($imagePath, 0644);
                
                if (!is_readable($imagePath)) {
                    \Log::error('Cannot read picture card image file after permission fix', [
                        'picture_card_id' => $pictureCard->id,
                        'file_path' => $imagePath
                    ]);
                    return redirect()->back()->with('error', 'Image file is not readable. Please check file permissions.');
                }
            }

            if (!is_writable($imagePath)) {
                \Log::warning('Picture card image file not writable, attempting to fix permissions', [
                    'picture_card_id' => $pictureCard->id,
                    'file_path' => $imagePath
                ]);
                chmod($imagePath, 0644);
                
                if (!is_writable($imagePath)) {
                    \Log::error('Cannot write to picture card image file after permission fix', [
                        'picture_card_id' => $pictureCard->id,
                        'file_path' => $imagePath
                    ]);
                    return redirect()->back()->with('error', 'Image file is not writable. Please check file permissions.');
                }
            }

            // Check and fix directory permissions
            $imageDir = dirname($imagePath);
            if (!is_writable($imageDir)) {
                \Log::warning('Picture card image directory not writable, attempting to fix permissions', [
                    'picture_card_id' => $pictureCard->id,
                    'directory' => $imageDir
                ]);
                chmod($imageDir, 0755);
                
                if (!is_writable($imageDir)) {
                    \Log::error('Cannot write to picture card image directory after permission fix', [
                        'picture_card_id' => $pictureCard->id,
                        'directory' => $imageDir
                    ]);
                    return redirect()->back()->with('error', 'Image directory is not writable: ' . $imageDir);
                }
            }

            // Backup original
            $originalPath = str_replace('.', '_original.', $imagePath);
            if (!file_exists($originalPath)) {
                // Ensure the backup directory exists
                $backupDir = dirname($originalPath);
                if (!is_dir($backupDir)) {
                    \Log::debug('Creating backup directory', [
                        'picture_card_id' => $pictureCard->id,
                        'directory' => $backupDir
                    ]);
                    
                    if (!mkdir($backupDir, 0755, true)) {
                        \Log::error('Failed to create backup directory', [
                            'picture_card_id' => $pictureCard->id,
                            'directory' => $backupDir
                        ]);
                        return redirect()->back()->with('error', 'Cannot create backup directory: ' . $backupDir);
                    }
                }
                
                // Create backup copy
                if (!copy($imagePath, $originalPath)) {
                    \Log::error('Failed to create backup copy', [
                        'picture_card_id' => $pictureCard->id,
                        'source' => $imagePath,
                        'destination' => $originalPath
                    ]);
                    return redirect()->back()->with('error', 'Failed to create backup copy of image');
                }
                
                \Log::debug('Backup created successfully', [
                    'picture_card_id' => $pictureCard->id,
                    'backup_path' => $originalPath
                ]);
            }

            // Create optimized version
            $image = imagecreatefromstring(file_get_contents($imagePath));
            $originalWidth = imagesx($image);
            $originalHeight = imagesy($image);

            // Resize if wider than 1200px
            if ($originalWidth > 1200) {
                $newWidth = 1200;
                $newHeight = ($originalHeight * $newWidth) / $originalWidth;
                $resized = imagecreatetruecolor($newWidth, $newHeight);
                imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
                
                // Save optimized version
                imagejpeg($resized, $imagePath, 85);
                imagedestroy($resized);
            }
            
            imagedestroy($image);

            // Further optimize with Spatie (if proc_open is available)
            if (function_exists('proc_open')) {
                try {
                    $optimizerChain = OptimizerChainFactory::create();
                    $optimizerChain->optimize($imagePath);
                    \Log::debug('Spatie optimization completed', ['picture_card_id' => $pictureCard->id]);
                } catch (\Exception $spatieError) {
                    \Log::warning('Spatie optimization failed, image already optimized by resize', [
                        'picture_card_id' => $pictureCard->id,
                        'spatie_error' => $spatieError->getMessage()
                    ]);
                }
            } else {
                \Log::info('proc_open not available, skipping Spatie optimization (image already optimized by resize)', [
                    'picture_card_id' => $pictureCard->id
                ]);
            }

            \Log::debug('Image optimization completed', ['picture_card_id' => $pictureCard->id]);

            return redirect()->back()->with('success', 'Image optimized successfully.');

        } catch (\Exception $e) {
            \Log::error('Image optimization failed', [
                'picture_card_id' => $pictureCard->id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'image_path' => $imagePath ?? 'N/A'
            ]);

            return redirect()->back()->with('error', 'Failed to optimize image. Error: ' . $e->getMessage());
        }
    }
}
```

---

## `routes/web.php`

```php
<?php

use App\Http\Controllers\Admin\AdminAdventurerApplicationController;
use App\Http\Controllers\Admin\AlbumController;
use App\Http\Controllers\Admin\ImageController;
use App\Http\Controllers\Admin\JumbotronController;
use App\Http\Controllers\Admin\SitemapController;
use App\Http\Controllers\Admin\DesignSystemController;
use App\Http\Controllers\AdventurerApplicationController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\HymnController;
use App\Http\Controllers\LivestreamController;
use App\Http\Controllers\MembershipController;
use App\Http\Controllers\SabbathSchoolController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\KitchenSinkController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/kitchen-sink', [KitchenSinkController::class, 'show'])->name('kitchen-sink')->middleware('auth');
Route::post('/kitchen-sink/save', [KitchenSinkController::class, 'saveLayout'])->name('kitchen-sink.save')->middleware('auth');

Auth::routes();

$middleware = App::environment('staging') ? ['auth'] : [];

Route::middleware($middleware)->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Main Site Pages
    |
    | These routes are for the main informational pages of the website that
    | are accessible to all visitors.
    |--------------------------------------------------------------------------
    */

    Route::controller(SiteController::class)->group(function () {
        // Displays the homepage
        Route::get('/', 'index')
            ->name('index');

        // Displays the about page
        Route::get('/about', 'about')
            ->name('about');

        // Displays the contact page
        Route::get('/contact', 'contact')
            ->name('contact');
        Route::post('/contact', 'contact');

        // Displays the VTS page
        Route::get('/vts', 'vts')
            ->name('vts');

        // Displays the donate page
        Route::get('/donate', 'donate')
            ->name('donate');

        // Displays the staff page
        Route::get('/staff', 'staff')
            ->name('staff');

        // Displays the ministries page
        Route::get('/ministries', 'ministries')
            ->name('ministries');


    });

    /*
    |--------------------------------------------------------------------------
    | Gallery Routes
    |
    | Routes for browsing photo albums and viewing individual images.
    |--------------------------------------------------------------------------
    */
    Route::controller(GalleryController::class)->prefix('gallery')->group(function () {
        // Displays the main gallery page
        Route::get('/', 'index')
            ->name('gallery');

        // Shows a specific album's details
        Route::get('/{album}', 'albumDetails')
            ->name('gallery.album');

        // Displays a single image from an album
        Route::get('/{album}/{image}', 'viewImage')
            ->name('gallery.image');
    });

    /*
    |--------------------------------------------------------------------------
    | Calendar Routes
    |
    | Routes for viewing the church's event calendar.
    |--------------------------------------------------------------------------
    */
    Route::controller(CalendarController::class)->prefix('calendar')->as('calendar.')->group(function () {
        // Displays the main calendar view
        Route::get('/', 'index')
            ->name('index');

        // Shows a specific event from the calendar
        Route::get('/view/{id}', 'view')
            ->name('view');
    });

    /*
    |--------------------------------------------------------------------------
    | Hymn Routes
    |
    | Routes for the digital hymn book.
    |--------------------------------------------------------------------------
    */
    Route::controller(HymnController::class)->prefix('hymns')->as('hymns.')->group(function () {
        // Displays the hymn book index
        Route::get('/', 'index')
            ->name('index');

        // Shows a specific hymn
        Route::get('/{id}', 'show')
            ->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | Sabbath School Study Routes
    |
    | Routes for accessing Sabbath School lesson materials.
    |--------------------------------------------------------------------------
    */
    Route::prefix('study')->as('study.')->group(function () {
        // Redirects to the current quarter's lesson book
        Route::get('/', function () {
            $currentQuarter = config('sabbathschool.current_quarter');

            return redirect()->route('study.book', ['lang' => 'fr', 'yearQuarter' => $currentQuarter]);
        })->name('index');

        Route::controller(SabbathSchoolController::class)->group(function () {
            // Displays the lesson book for a specific language and quarter
            Route::get('/{lang}/{yearQuarter}', 'book')
                ->name('book')
                ->where(['lang' => 'fr|en', 'yearQuarter' => '\d{4}q[1-4]']);

            // Fetches a specific lesson chapter
            Route::get('/{lang}/{yearQuarter}/{chapterDir}', 'fetchLesson')
                ->name('chapter')
                ->where(['lang' => 'fr|en', 'yearQuarter' => '\d{4}q[1-4]', 'chapterDir' => '\d{2}']);

            // Displays a specific day's lesson
            Route::get('/{lang}/{yearQuarter}/{lessonNumber}/{lessonDay}', 'day')
                ->name('day')
                ->where(['lang' => 'fr|en', 'yearQuarter' => '\d{4}q[1-4]', 'lessonNumber' => '\d{2}', 'lessonDay' => '\d{2}']);
        });
    });

    /*
    |--------------------------------------------------------------------------
    | Public Adventurer Application Routes
    |
    | Routes for the public-facing Adventurer club application form.
    |--------------------------------------------------------------------------
    */
    Route::controller(AdventurerApplicationController::class)->prefix('adventurers')->as('adventurers.')->group(function () {
        // Displays the Adventurer club index page
        Route::get('/', 'index')
            ->name('index');

        // Shows the application form
        Route::get('/apply', 'create')
            ->name('apply.create');
        Route::post('/apply', 'store')
            ->name('apply.store');

        // Displays a success message after application
        Route::get('/apply/success', 'success')
            ->name('apply.success');

        // Shows the trip permission form
        Route::get('/trip', 'showTripPermissionForm')
            ->name('trip.create');
        Route::post('/trip', 'submitTripPermission')
            ->name('trip.store');

        // Displays a success message after submitting trip permission
        Route::get('/trip/success', 'tripPermissionSuccess')
            ->name('trip.success');
    });


    /*
    |--------------------------------------------------------------------------
    | Youth (Jeunesse), Livestream, and Public Event Routes
    |--------------------------------------------------------------------------
    */

    // Shows the livestream page
    Route::get('/livestream', [LivestreamController::class, 'show'])
        ->name('livestream.show');

    Route::controller(\App\Http\Controllers\EventController::class)->prefix('events')->as('events.')->group(function () {
        // Displays the list of public events
        Route::get('/', 'index')
            ->name('index');

        // Shows a single public event
        Route::get('/{event}', 'show')
            ->name('show');
    });

}); // End of public $middleware group

/*
|--------------------------------------------------------------------------
| Admin Routes
|
| These routes are for the administrative panel and are protected by
| authentication middleware. They provide functionality for managing
| the site's content and applications.
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth']], function () {

    // Redirects from /admin to /admin/dashboard
    Route::get('/', function () {
        return redirect()->route('admin.dashboard');
    })->name('index');

    // Displays the main admin dashboard
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Design System Routes
    |--------------------------------------------------------------------------
    */
    Route::controller(DesignSystemController::class)->prefix('design-system')->as('design-system.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/{page}', 'show')->name('show');
    });

    /*
    |--------------------------------------------------------------------------
    | Adventurer Applications Management
    |--------------------------------------------------------------------------
    */
    Route::controller(AdminAdventurerApplicationController::class)->prefix('adventurers')->as('adventurers.')->group(function () {
        // Lists all adventurer applications
        Route::get('/', 'index')
            ->name('index');

        // Exports applications to a file
        Route::get('/export', 'export')
            ->name('export');

        // Generates a printable view of applications
        Route::get('/print', 'print')
            ->name('print');

        // Trip Management
        Route::prefix('trips')->as('trips.')->group(function () {
            // Lists all trip permission forms
            Route::get('/', 'listTrips')
                ->name('index');

            // Generates a printable view of trip permissions
            Route::get('/print', 'printTrips')
                ->name('print');

            // Shows a specific trip permission
            Route::get('/{tripPermission}', 'showTrip')
                ->name('show');

            // Deletes a trip permission
            Route::delete('/{tripPermission}', 'destroyTrip')
                ->name('destroy');
        });

        // Individual Application Actions
        // Shows a single application
        Route::get('/{application}', 'show')
            ->name('show');

        // Shows the form to edit an application
        Route::get('/{application}/edit', 'edit')
            ->name('edit');

        // Updates an application
        Route::put('/{application}', 'update')
            ->name('update');

        // Deletes an application
        Route::delete('/{application}', 'destroy')
            ->name('destroy');

        // Downloads an application as a file
        Route::get('/{application}/download', 'download')
            ->name('download');
    });

    /*
    |--------------------------------------------------------------------------
    | File Upload Management
    |--------------------------------------------------------------------------
    */
    Route::controller(FileUploadController::class)->prefix('dashboard')->as('dashboard.')->group(function () {
        // Shows the file upload form
        Route::get('/upload', 'showUploadForm')
            ->name('upload.form');

        // Handles the file upload process
        Route::post('/upload', 'upload')
            ->name('upload');

        // Lists uploaded files
        Route::get('/files', 'listFiles')
            ->name('files.index');

        // Shows a specific uploaded file
        Route::get('/files/{id}', 'showFile')
            ->name('files.show');
    });

    /*
    |--------------------------------------------------------------------------
    | LiveStream Management
    |--------------------------------------------------------------------------
    */
    Route::controller(LivestreamController::class)->prefix('livestream')->as('livestream.')->group(function () {
        // Shows the livestream management page
        Route::get('/', 'show')
            ->name('show');

        // Updates the livestream settings
        Route::put('/', 'update')
            ->name('update');
    });

    /*
    |--------------------------------------------------------------------------
    | Album & Image Management
    |--------------------------------------------------------------------------
    */
    // Handles uploading images to a specific album
    Route::post('/albums/{album}/upload-image', [AlbumController::class, 'uploadImage'])
        ->name('albums.uploadImage');

    // Defines CRUD routes for albums
    Route::resource('albums', AlbumController::class);

    // Deletes a specific image
    Route::delete('/images/{image}', [ImageController::class, 'destroy'])
        ->name('images.destroy');

    // Optimizes a specific image
    Route::post('/images/{image}/optimize', [ImageController::class, 'optimize'])
        ->name('images.optimize');

    /*
    |--------------------------------------------------------------------------
    | Event Management
    |--------------------------------------------------------------------------
    */
    // Defines CRUD routes for events
    Route::resource('events', \App\Http\Controllers\Admin\EventController::class);

    /*
    |--------------------------------------------------------------------------
    | Homepage Gallery Management
    |--------------------------------------------------------------------------
    */
    Route::controller(\App\Http\Controllers\Admin\HomepageGalleryController::class)->prefix('homepage-gallery')->as('homepage-gallery.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'update')->name('update');
        Route::delete('/{image}', 'remove')->name('remove');
        Route::post('/set-defaults', 'setDefaults')->name('set-defaults');
    });

    /*
    |--------------------------------------------------------------------------
    | Jumbotron Management
    |--------------------------------------------------------------------------
    */
    Route::controller(JumbotronController::class)->prefix('jumbotron')->as('jumbotron.')->group(function () {
        // Shows the jumbotron management page
        Route::get('/', 'index')
            ->name('index');

        // Updates the jumbotron settings
        Route::post('/', 'store')
            ->name('store');

        // Optimize the current jumbotron image
        Route::post('/optimize', 'optimize')
            ->name('optimize');

        // Optimize the current horizontal card image
        Route::post('/optimize-horizontal', 'optimizeHorizontal')
            ->name('optimize-horizontal');
    });

    /*
    |--------------------------------------------------------------------------
    | Sitemap Generation
    |--------------------------------------------------------------------------
    */
    // Displays the admin sitemap
    Route::get('/sitemap', [SitemapController::class, 'index'])
        ->name('sitemap');

    /*
    |--------------------------------------------------------------------------
    | Carousel Management
    |--------------------------------------------------------------------------
    */
    Route::controller(\App\Http\Controllers\Admin\CarouselController::class)->prefix('carousel')->as('carousel.')->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/', 'store')->name('store');
        Route::put('/{carousel}', 'update')->name('update');
        Route::delete('/{carousel}', 'destroy')->name('destroy');
        Route::post('/update-order', 'updateOrder')->name('update-order');
    });

    /*
    |--------------------------------------------------------------------------
    | Picture Cards Management
    |--------------------------------------------------------------------------
    */
    // Defines CRUD routes for picture cards
    Route::resource('picture-cards', \App\Http\Controllers\Admin\PictureCardController::class);
    
    // Optimizes a picture card image
    Route::post('/picture-cards/{pictureCard}/optimize', [\App\Http\Controllers\Admin\PictureCardController::class, 'optimizeImage'])
        ->name('picture-cards.optimize');

    /*
    |--------------------------------------------------------------------------
    | Community Impact Images Management
    |--------------------------------------------------------------------------
    */
    // Defines CRUD routes for community impact images (homepage section)

}); // End of main admin group
```

---

## `database/migrations/2025_07_18_144150_add_size_columns_to_images_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->string('path_original')->nullable()->after('file_path');
            $table->unsignedBigInteger('original_size')->nullable()->after('path_original');
            $table->unsignedBigInteger('optimized_size')->nullable()->after('original_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('images', function (Blueprint $table) {
            $table->dropColumn(['path_original', 'original_size', 'optimized_size']);
        });
    }
};
```

---

## `database/migrations/2025_09_26_000601_add_optimization_fields_to_jumbotron_settings.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jumbotron_settings', function (Blueprint $table) {
            $table->string('image_original_path')->nullable()->after('image_path');
            $table->unsignedBigInteger('image_original_size')->nullable()->after('image_original_path');
            $table->unsignedBigInteger('image_optimized_size')->nullable()->after('image_original_size');
            $table->timestamp('image_optimized_at')->nullable()->after('image_optimized_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jumbotron_settings', function (Blueprint $table) {
            $table->dropColumn([
                'image_original_path',
                'image_original_size',
                'image_optimized_size',
                'image_optimized_at',
            ]);
        });
    }
};
```

---

## `database/migrations/2025_09_26_000945_add_horizontal_optimization_fields_to_jumbotron_settings.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('jumbotron_settings', function (Blueprint $table) {
            $table->string('horizontal_image_original_path')->nullable()->after('horizontal_card_image_path');
            $table->unsignedBigInteger('horizontal_image_original_size')->nullable()->after('horizontal_image_original_path');
            $table->unsignedBigInteger('horizontal_image_optimized_size')->nullable()->after('horizontal_image_original_size');
            $table->timestamp('horizontal_image_optimized_at')->nullable()->after('horizontal_image_optimized_size');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jumbotron_settings', function (Blueprint $table) {
            $table->dropColumn([
                'horizontal_image_original_path',
                'horizontal_image_original_size',
                'horizontal_image_optimized_size',
                'horizontal_image_optimized_at',
            ]);
        });
    }
};
```
