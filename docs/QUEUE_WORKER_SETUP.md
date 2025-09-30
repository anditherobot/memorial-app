# Queue Worker Setup - IMPORTANT

## ⚠️ CRITICAL: Queue Worker Must Be Running

**Image optimization will NOT work without the queue worker running.**

When you upload images or click "Optimize Selected", the optimization jobs are added to a queue. These jobs are processed by a background worker that must be manually started.

## Quick Start

### Development

Open a **separate terminal** and run:

```bash
php artisan queue:work
```

Keep this terminal running while you use the application.

## What Happens Without Queue Worker

### ❌ Without Queue Worker
```
User uploads image
    ↓
Image saved to storage
    ↓
Optimization job added to queue
    ↓
❌ Job sits in queue forever
    ↓
Image shows "✗ Not Optimized" forever
    ↓
No thumbnails created
    ↓
No file size reduction
```

### ✅ With Queue Worker
```
User uploads image
    ↓
Image saved to storage
    ↓
Optimization job added to queue
    ↓
✅ Queue worker picks up job (within 1-2 seconds)
    ↓
Creates thumbnail (800px, <200KB)
    ↓
Creates web-optimized (1920px, <2MB)
    ↓
Saves to database
    ↓
User refreshes page → sees "✓ Optimized"
```

## How to Start Queue Worker

### Option 1: Basic Development Setup

```bash
# Navigate to project directory
cd /c/Users/expan/Desktop/memorial

# Start queue worker (keeps running)
php artisan queue:work
```

**Output you should see:**
```
[2025-09-30 18:11:59] Processing: App\Jobs\ProcessImageOptimization
[2025-09-30 18:12:00] Processed:  App\Jobs\ProcessImageOptimization
```

### Option 2: With Timeout and Retry Settings

```bash
# Process jobs with 60 second timeout, retry failed jobs 3 times
php artisan queue:work --tries=3 --timeout=60
```

### Option 3: Process One Job at a Time (Testing)

```bash
# Process just one job then stop
php artisan queue:work --once
```

## Checking If Queue Worker Is Running

### Windows (Git Bash)
```bash
ps aux | grep "queue:work"
```

### Check for Running Jobs
```bash
php artisan queue:listen --timeout=0 --tries=1
```

### View Queue Status
```bash
# See pending jobs
php artisan queue:monitor

# Or check the database directly
sqlite3 database/database.sqlite "SELECT COUNT(*) FROM jobs;"
```

## Common Issues

### Issue: Images Stay "Not Optimized"

**Symptoms:**
- Upload image
- Wait 1 minute
- Refresh page
- Still shows "✗ Not Optimized"

**Cause:** Queue worker is not running

**Solution:**
```bash
# Check if queue worker is running
ps aux | grep queue:work

# If not running, start it
php artisan queue:work
```

### Issue: "Optimizing..." Button Never Completes

**Symptoms:**
- Click "Optimize Selected"
- Button shows "Optimizing..."
- Page never reloads
- Button stays disabled

**Cause:** Queue worker crashed or not running

**Solution:**
1. Check queue worker terminal for errors
2. Restart queue worker:
   ```bash
   # Press Ctrl+C to stop
   # Then start again
   php artisan queue:work
   ```

### Issue: Jobs Failing Silently

**Check failed jobs:**
```bash
# List failed jobs
php artisan queue:failed

# View failed job details
php artisan queue:failed-table
php artisan migrate
php artisan queue:failed
```

**Retry failed jobs:**
```bash
# Retry all failed jobs
php artisan queue:retry all

# Retry specific job
php artisan queue:retry 1
```

### Issue: Queue Worker Stops After Each Job

**Symptom:** Worker exits after processing one job

**Cause:** Using `--once` flag

**Solution:** Use `queue:work` without `--once`:
```bash
php artisan queue:work
```

## Production Setup

### Option 1: Supervisor (Linux - Recommended)

Create `/etc/supervisor/conf.d/memorial-queue.conf`:

```ini
[program:memorial-queue-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/memorial/artisan queue:work --sleep=3 --tries=3 --timeout=60
autostart=true
autorestart=true
stopasserver=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/memorial/storage/logs/worker.log
stopwaitsecs=3600
```

**Start supervisor:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start memorial-queue-worker:*
```

**Check status:**
```bash
sudo supervisorctl status memorial-queue-worker:*
```

### Option 2: systemd (Linux Alternative)

Create `/etc/systemd/system/memorial-queue.service`:

```ini
[Unit]
Description=Memorial Queue Worker
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/path/to/memorial
ExecStart=/usr/bin/php artisan queue:work --sleep=3 --tries=3 --timeout=60
Restart=always
RestartSec=3

[Install]
WantedBy=multi-user.target
```

**Enable and start:**
```bash
sudo systemctl daemon-reload
sudo systemctl enable memorial-queue
sudo systemctl start memorial-queue
```

**Check status:**
```bash
sudo systemctl status memorial-queue
```

### Option 3: Windows Task Scheduler

1. Open Task Scheduler
2. Create Basic Task
3. Name: "Memorial Queue Worker"
4. Trigger: At startup
5. Action: Start a program
6. Program: `C:\path\to\php.exe`
7. Arguments: `artisan queue:work --sleep=3 --tries=3 --timeout=60`
8. Start in: `C:\Users\expan\Desktop\memorial`

## Monitoring Queue Worker

### View Logs in Real-Time

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Filter for optimization jobs
tail -f storage/logs/laravel.log | grep ProcessImageOptimization
```

### Check Queue Health

```bash
# Count pending jobs
sqlite3 database/database.sqlite "SELECT COUNT(*) FROM jobs;"

# Count failed jobs
sqlite3 database/database.sqlite "SELECT COUNT(*) FROM failed_jobs;"

# View recent failed jobs
sqlite3 database/database.sqlite "SELECT id, failed_at, exception FROM failed_jobs ORDER BY failed_at DESC LIMIT 5;"
```

### Performance Metrics

```bash
# Average job processing time
php artisan queue:monitor

# View queue size
php artisan queue:listen --timeout=0
```

## Testing Queue Worker

### Test 1: Process a Job Manually

```bash
# Open tinker
php artisan tinker

# Dispatch a job
$media = Media::latest()->first();
ProcessImageOptimization::dispatch($media);

# Exit tinker
exit
```

Watch the queue worker terminal - you should see:
```
[timestamp] Processing: App\Jobs\ProcessImageOptimization
[timestamp] Processed:  App\Jobs\ProcessImageOptimization
```

### Test 2: Verify Job Completed

```bash
sqlite3 database/database.sqlite "
SELECT m.id, m.original_filename,
       COUNT(CASE WHEN md.type = 'thumbnail' THEN 1 END) as has_thumb,
       COUNT(CASE WHEN md.type = 'web-optimized' THEN 1 END) as has_web
FROM media m
LEFT JOIN media_derivatives md ON m.id = md.media_id
WHERE m.id = (SELECT MAX(id) FROM media)
GROUP BY m.id;
"
```

**Expected output:**
```
id|original_filename|has_thumb|has_web
28|IMG_9499.jpg|1|1
```

## Best Practices

### Development
1. **Always run queue worker in separate terminal**
2. Keep terminal visible to see job processing
3. Restart worker after code changes to `ProcessImageOptimization.php`

### Production
1. Use Supervisor or systemd (not manual terminal)
2. Set up automatic restarts
3. Monitor failed jobs daily
4. Set up log rotation for `storage/logs/worker.log`
5. Use multiple workers for high traffic: `numprocs=4`

### Debugging
1. Check queue worker is running: `ps aux | grep queue:work`
2. Check Laravel logs: `tail -f storage/logs/laravel.log`
3. Check failed jobs: `php artisan queue:failed`
4. Verify files exist: `ls storage/app/public/media/derivatives/`

## Summary

### ✅ Required for Optimization
- Queue worker **must be running**
- Start with: `php artisan queue:work`
- Keep terminal open during use
- Set up Supervisor/systemd for production

### ⚠️ Without Queue Worker
- Jobs pile up in database
- No optimization happens
- Images stay "Not Optimized"
- Gallery loads slowly (original files)

### 🎯 With Queue Worker
- Jobs process within 1-2 seconds
- Automatic optimization after upload
- Gallery loads fast (thumbnails)
- Lightbox opens quickly (web-optimized)
- Bandwidth savings of 85-97%

## Quick Reference Commands

```bash
# Start queue worker
php artisan queue:work

# Stop queue worker
Press Ctrl+C

# Check if running
ps aux | grep queue:work

# View pending jobs
sqlite3 database/database.sqlite "SELECT * FROM jobs;"

# View failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Clear all jobs (nuclear option)
php artisan queue:clear
```

## Need Help?

If optimization isn't working:
1. ✅ Is queue worker running? → `ps aux | grep queue:work`
2. ✅ Are there errors in logs? → `tail -f storage/logs/laravel.log`
3. ✅ Are jobs failing? → `php artisan queue:failed`
4. ✅ Do files exist? → `ls storage/app/public/media/derivatives/`
5. ✅ Does storage symlink exist? → `php artisan storage:link`
