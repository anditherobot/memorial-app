# Application White Paper: Memorial Platform

**Version:** 1.1
**Date:** 2025-10-03

## 1. Introduction & System Overview

### 1.1. Purpose
This document provides a detailed technical specification of the Memorial Platform application. The platform is designed as a dual-environment system: a public-facing memorial website and a comprehensive backend administration panel for content and system management.

### 1.2. Technology Stack
- **Backend Framework**: Laravel (PHP)
- **Frontend Tooling**: Vite, Tailwind CSS, HTMX (for dynamic admin interfaces)
- **Database**: Relational (e.g., MySQL, PostgreSQL)
- **Testing**: PHPUnit (Backend), Playwright (End-to-End)
- **Deployment**: Nginx, PHP-FPM, systemd-managed queue worker.

### 1.3. Architectural Style
The system uses a Model-View-Controller (MVC) architecture. A key feature is its extensive use of a background job queue to offload time-consuming tasks (like image processing), ensuring a responsive user experience in both the public and admin areas.

---

## 2. Public-Facing Application Features

### 2.1. Homepage (`HomeController`)
- **Feature Description**: Serves as the central, dynamic landing page. It aggregates various types of content into a single, cohesive view.
- **Technical Implementation**: The `HomeController` fetches and combines data from multiple models:
    - `MemorialContent` for primary text (biography, name, dates).
    - `Media` for a gallery of recent, public photos.
    - `MemorialEvent` for upcoming events.
    - `Post` for recent news or updates.
    It includes graceful fallbacks to default content if no custom content is available.
- **Key File Paths**: `app/Http/Controllers/HomeController.php`, `resources/views/home.blade.php`

### 2.2. Public Gallery (`GalleryController`)
- **Feature Description**: A simple, paginated view of all media items that have been marked as public.
- **Key File Paths**: `app/Http/Controllers/GalleryController.php`, `resources/views/gallery/index.blade.php`

### 2.3. Public Updates & Posts (`PostController`)
- **Feature Description**: A blog-style section for viewing published updates and announcements, with a main index and individual detail pages.
- **Key File Paths**: `app/Http/Controllers/PostController.php`, `resources/views/updates/`

### 2.4. Wish Wall & Guestbook (`WishController`)
- **Feature Description**: Allows public users to view approved "wishes" and submit their own through a form.
- **Technical Implementation**: New submissions are saved with `is_approved` set to `false`. They only become visible on the public wall after an administrator approves them via the admin panel. Includes a honeypot field for spam prevention.
- **Key File Paths**: `app/Http/Controllers/WishController.php`, `resources/views/wishes/`

### 2.5. User-Driven Photo Uploads
- **Feature Description**: The application provides two distinct methods for public users to upload photos.
- **Technical Implementation**:
    1.  **Simple Public Upload (`UploadController`)**: A straightforward form for uploading a single image, which is immediately marked as public and processed.
    2.  **Authenticated Bulk Upload (`PhotoController`)**: A more advanced uploader for logged-in users to submit multiple images at once. It tracks the processing status of each individual photo.
- **Key File Paths**: `app/Http/Controllers/UploadController.php`, `app/Http/Controllers/PhotoController.php`

---

## 3. Administration Panel Features

A comprehensive backend panel for site management, accessible only to authenticated administrators.

### 3.1. Admin Dashboard (`AdminController`)
- **Feature Description**: The central landing page for the admin panel, providing at-a-glance statistics and summaries of recent site activity.
- **Technical Implementation**: Displays counts of pending wishes, total posts, and media items. Shows lists of recent uploads, pictures, and posts for quick review.
- **Key File Paths**: `app/Http/Controllers/AdminController.php`, `resources/views/admin/dashboard.blade.php`

### 3.2. Gallery & Media Management (`AdminGalleryController`)
- **Feature Description**: Full CRUD (Create, Read, Update, Delete) functionality for all media in the system.
- **Technical Implementation**:
    - **Bulk Uploads**: Allows uploading multiple images at once.
    - **Optimization**: Admins can manually trigger the `ProcessImageOptimization` job for selected images.
    - **Deletion**: Securely deletes a media item and all its associated derivative files from storage.
- **Key File Paths**: `app/Http/Controllers/AdminGalleryController.php`, `resources/views/admin/gallery.blade.php`

### 3.3. Post & Update Management (`AdminPostController`)
- **Feature Description**: Full CRUD functionality for creating, editing, and deleting posts/updates.
- **Technical Implementation**: Allows admins to manage titles, body content, and publication status. Includes functionality to attach or remove a cover image for each post.
- **Key File Paths**: `app/Http/Controllers/AdminPostController.php`, `resources/views/updates/admin/`

### 3.4. Wish Moderation (`WishController`)
- **Feature Description**: An interface for administrators to review and moderate user-submitted wishes.
- **Technical Implementation**: The `adminIndex`, `approve`, and `destroy` methods in the `WishController` allow admins to view a queue of pending wishes, approve them (setting `is_approved` to `true`), or delete them.
- **Key File Paths**: `app/Http/Controllers/WishController.php` (admin methods), `resources/views/wishes/admin.blade.php`

### 3.5. Advanced Task Management Board (`AdminTaskController`)
- **Feature Description**: A feature-rich, Kanban-style task management board for internal project and administrative tasks.
- **Technical Implementation**: This is far more than a simple to-do list. It supports:
    - **Statuses**: `todo`, `in_progress`, `completed`, `blocked`.
    - **Priorities**: `low`, `medium`, `high`, `urgent`.
    - **Assignments**: Tasks can be assigned to specific users.
    - **Details**: Supports categories, due dates, and notes.
    The interface is dynamic, likely powered by HTMX or a similar library, allowing for drag-and-drop status changes.
- **Key File Paths**: `app/Http/Controllers/AdminTaskController.php`, `resources/views/admin/tasks/index.blade.php`

---

## 4. Core System Models & Logic

This section covers the underlying data models and logic that power the features above.

### 4.1. User & Authentication
- **Model**: `app/Models/User.php`
- **Description**: Standard Laravel user model. An `is_admin` flag likely controls access to the admin panel.

### 4.2. Media Sub-system
- **Models**: `Media`, `Photo`, `MediaDerivative`
- **Description**: A flexible system where `Media` is the original file, `MediaDerivative` is a processed version (e.g., thumbnail), and `Photo` is a user-facing abstraction that links them. Asynchronous jobs (`ProcessImage`, `ProcessImageOptimization`) handle all processing.

### 4.3. Content Models
- **Models**: `MemorialContent`, `MemorialEvent`, `Post`, `Wish`, `Task`
- **Description**: These models represent the primary data entities for both public content and internal administrative tasks.

---

## 5. System Architecture & Deployment
(Content from the previous version of the white paper is still accurate here and can be retained).

