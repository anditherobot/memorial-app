# Image Optimization Scenarios

## Overview
This document outlines the image optimization workflows for the Media Management system.

## Scenario 1: New Image Upload (Automatic Optimization)

### User Action
1.  An admin uploads a new image file via the `/admin/gallery` page.

### System Process
1.  The `MediaController@upload` method receives the file.
2.  A `ProcessUploadedImage` job is dispatched to the queue.
3.  **Job Execution (`ProcessUploadedImage`):**
    *   A new `Media` record is created in the database.
    *   The original image is stored in `storage/app/media/originals`.
    *   A `GeneratePoster` job is dispatched to create a preview image.
    *   An `OptimizeImage` job is dispatched to create standard web and thumbnail sizes.
4.  **Job Execution (`OptimizeImage`):**
    *   **Web Derivative:** Creates a 1920px wide JPEG (85% quality).
    *   **Thumbnail Derivative:** Creates a 400px wide JPEG (85% quality).
    *   Each derivative is saved to `storage/app/media/derivatives` and a `MediaDerivative` record is created.

### Expected Outcome
- The new image appears in the gallery.
- It automatically has "✓ Optimized" and "✓ Thumbnail" badges because the derivatives were created on upload.

---

## Scenario 2: Bulk Optimization from Gallery

### User Action
1.  An admin navigates to the `/admin/gallery` page.
2.  The admin selects one or more images using the checkboxes. Images that already have an optimized web version are still selectable but will be skipped by the backend.
3.  The "Optimize Selected" button becomes active and displays the number of selected images (e.g., "Optimize 5 Selected"). It may also show how many of the selected images actually require optimization (e.g., "Optimize 5 Selected (3 need optimization)").
4.  The admin clicks the "Optimize Selected" button.

### System Process
1.  **Frontend:**
    *   The button text changes to "Optimizing...".
    *   A `POST` request is sent to `/admin/gallery/optimize` with an array of selected `media_ids`.
2.  **Backend (`MediaController@optimize`):**
    *   The incoming `media_ids` are validated.
    *   The controller iterates through each `media_id`.
    *   For each image, it checks if a 'web' derivative already exists.
    *   If the 'web' derivative does **not** exist, it dispatches an `OptimizeImage` job.
    *   If the 'web' derivative **does** exist, it skips that image.
    *   The controller returns a JSON response summarizing the action (e.g., "Optimization started for 3 images. 2 were already optimized.").
3.  **Frontend (on successful response):**
    *   The page reloads.
    *   A toast notification appears confirming the result from the server.

### Expected Outcome
- After the page reloads, the newly optimized images now display the "✓ Optimized" badge.
- The selection checkboxes are cleared.
- A success notification provides feedback on the operation.
