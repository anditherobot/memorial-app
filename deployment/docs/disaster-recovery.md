# Memorial Website - Disaster Recovery Guide

This guide covers backup and restore procedures for the Memorial Website, including complete system recovery scenarios.

## Backup Strategy

### Automated Backups

The system creates daily backups at 2 AM using the `/usr/local/bin/memorial-backup` script:

```bash
# Daily backup cron job
0 2 * * * /usr/local/bin/memorial-backup
```

### What Gets Backed Up

1. **SQLite Database** - Complete database dump using `.backup` command
2. **Storage Directory** - All uploaded files, logs, and cache
3. **Environment Configuration** - `.env` file (sensitive data)
4. **Metadata** - Backup timestamp, Laravel version, server info

### Backup Locations

- **Local**: `/home/memorial/backups/`
- **Remote**: Configured via rclone (optional)
- **Retention**: 30 days local, configurable for remote

## Backup Operations

### Manual Backup

```bash
# Create immediate backup
sudo -u memorial /usr/local/bin/memorial-backup

# List available backups
sudo -u memorial /usr/local/bin/memorial-backup list

# Verify backup integrity
sudo -u memorial /usr/local/bin/memorial-backup verify memorial_backup_20241201_120000.tar.gz
```

### Remote Storage Setup

Configure rclone for offsite backups:

```bash
# Setup rclone (as memorial user)
sudo -u memorial rclone config

# Test remote connection
sudo -u memorial rclone ls remote:memorial-backups
```

Supported remote storage:
- AWS S3
- Google Cloud Storage
- Microsoft Azure
- Dropbox
- Any S3-compatible storage

## Restore Procedures

### Complete System Restore

**Scenario**: Server completely destroyed, restoring to new server

1. **Setup New Server**
   ```bash
   # Follow server-setup.md guide
   # Install all dependencies and configure basic system
   ```

2. **Deploy Application**
   ```bash
   # Clone repository and setup application
   cd /home/memorial
   git clone <repository-url> .
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   ```

3. **Download Backup**
   ```bash
   # From remote storage
   sudo -u memorial rclone copy remote:memorial-backups/memorial_backup_latest.tar.gz /home/memorial/backups/

   # Or from local copy if available
   ```

4. **Restore Data**
   ```bash
   # Restore from backup
   sudo -u memorial /usr/local/bin/memorial-backup restore /home/memorial/backups/memorial_backup_20241201_120000.tar.gz
   ```

5. **Verify and Start Services**
   ```bash
   # Check application
   cd /home/memorial
   php artisan config:clear
   php artisan migrate --force

   # Start services
   sudo systemctl enable memorial-worker
   sudo systemctl start memorial-worker
   sudo systemctl restart nginx php8.3-fpm
   ```

### Partial Data Restore

**Scenario**: Database corruption, files intact

```bash
# Stop services
sudo systemctl stop memorial-worker

# Extract just the database
mkdir -p /tmp/restore
cd /tmp/restore
tar -xzf /home/memorial/backups/memorial_backup_20241201_120000.tar.gz

# Copy database
cp memorial_backup_*/database.sqlite /home/memorial/database/
chmod 664 /home/memorial/database/database.sqlite

# Clear caches and restart
cd /home/memorial/app
php artisan config:clear
sudo systemctl start memorial-worker
```

**Scenario**: Lost uploaded files, database intact

```bash
# Extract and restore storage only
mkdir -p /tmp/restore
cd /tmp/restore
tar -xzf /home/memorial/backups/memorial_backup_20241201_120000.tar.gz

# Restore storage
sudo rm -rf /home/memorial/storage
sudo cp -r memorial_backup_*/storage /home/memorial/
sudo chown -R memorial:www-data /home/memorial/storage
sudo chmod -R 775 /home/memorial/storage
```

### Point-in-Time Recovery

**Scenario**: Need to restore to specific time

```bash
# List available backups with timestamps
sudo -u memorial /usr/local/bin/memorial-backup list

# Choose backup closest to desired time
sudo -u memorial /usr/local/bin/memorial-backup restore memorial_backup_20241201_140000.tar.gz
```

## Database-Specific Recovery

### SQLite Database Repair

If database is corrupted but readable:

```bash
# Create backup of current state
cp /home/memorial/database/database.sqlite /tmp/corrupted.sqlite

# Attempt repair
cd /home/memorial
sqlite3 database/database.sqlite "PRAGMA integrity_check;"

# If integrity check fails, restore from backup
sudo -u memorial /usr/local/bin/memorial-backup restore memorial_backup_latest.tar.gz
```

### Database Migration Recovery

If migrations are broken:

```bash
# Reset migration table (DANGEROUS - only in emergency)
cd /home/memorial
sqlite3 database/database.sqlite "DROP TABLE IF EXISTS migrations;"

# Restore from backup and re-run migrations
sudo -u memorial /usr/local/bin/memorial-backup restore memorial_backup_latest.tar.gz
php artisan migrate --force
```

## File Recovery

### Individual File Recovery

Extract specific files from backup without full restore:

```bash
# Create temporary directory
mkdir -p /tmp/file_recovery
cd /tmp/file_recovery

# Extract backup
tar -xzf /home/memorial/backups/memorial_backup_20241201_120000.tar.gz

# Navigate to needed file
cd memorial_backup_*/storage/app/public/media

# Copy specific file
cp image.jpg /home/memorial/storage/app/public/media/
sudo chown memorial:www-data /home/memorial/storage/app/public/media/image.jpg
```

### Bulk File Recovery

Restore all files of a specific type:

```bash
# Extract backup
mkdir -p /tmp/bulk_recovery
cd /tmp/bulk_recovery
tar -xzf /home/memorial/backups/memorial_backup_20241201_120000.tar.gz

# Copy all images
find memorial_backup_*/storage -name "*.jpg" -o -name "*.png" -o -name "*.gif" | \
while read file; do
    cp "$file" /home/memorial/storage/app/public/media/
done

# Fix permissions
sudo chown -R memorial:www-data /home/memorial/storage/app/public/media/
```

## Testing Recovery Procedures

### Monthly Recovery Tests

1. **Create test environment**
   ```bash
   # Setup separate test server or container
   # Follow same setup procedures
   ```

2. **Test full restore**
   ```bash
   # Use latest backup to restore test environment
   # Verify all functionality works
   ```

3. **Document results**
   - Restoration time
   - Any issues encountered
   - Data integrity verification

### Automated Recovery Verification

```bash
#!/bin/bash
# Add to cron for weekly verification

BACKUP_FILE="/home/memorial/backups/$(ls -t /home/memorial/backups/memorial_backup_*.tar.gz | head -1)"
TEST_DIR="/tmp/recovery_test_$(date +%s)"

mkdir -p "$TEST_DIR"
cd "$TEST_DIR"

# Extract and verify
if tar -xzf "$BACKUP_FILE"; then
    echo "✓ Backup extraction successful"

    # Check database
    if sqlite3 memorial_backup_*/database.sqlite "SELECT COUNT(*) FROM users;" > /dev/null 2>&1; then
        echo "✓ Database integrity verified"
    else
        echo "✗ Database integrity check failed"
    fi

    # Check files
    if [ -d "memorial_backup_*/storage" ]; then
        echo "✓ Storage directory present"
    else
        echo "✗ Storage directory missing"
    fi
else
    echo "✗ Backup extraction failed"
fi

# Cleanup
rm -rf "$TEST_DIR"
```

## Emergency Contacts and Procedures

### Emergency Response Checklist

1. **Assess Damage**
   - [ ] Database accessible?
   - [ ] Files accessible?
   - [ ] Services running?
   - [ ] Recent backup available?

2. **Immediate Actions**
   - [ ] Stop affected services
   - [ ] Secure any remaining data
   - [ ] Notify stakeholders
   - [ ] Begin recovery process

3. **Recovery Steps**
   - [ ] Download latest backup
   - [ ] Verify backup integrity
   - [ ] Start restore process
   - [ ] Test functionality
   - [ ] Resume services

4. **Post-Recovery**
   - [ ] Document incident
   - [ ] Review backup procedures
   - [ ] Update recovery documentation
   - [ ] Schedule additional backups

### Recovery Time Objectives (RTO)

- **Database only**: 15 minutes
- **Complete application**: 1 hour
- **Full system rebuild**: 4 hours
- **From remote backup**: +30 minutes

### Recovery Point Objectives (RPO)

- **Maximum data loss**: 24 hours (daily backups)
- **Typical data loss**: <2 hours (if backup during incident)

## Backup Monitoring

### Health Checks

```bash
# Daily backup verification script
#!/bin/bash
BACKUP_DIR="/home/memorial/backups"
LATEST_BACKUP=$(ls -t "$BACKUP_DIR"/memorial_backup_*.tar.gz 2>/dev/null | head -1)

if [ -n "$LATEST_BACKUP" ]; then
    AGE=$(find "$LATEST_BACKUP" -mtime +1)
    if [ -n "$AGE" ]; then
        echo "WARNING: Latest backup is older than 24 hours"
        # Send alert (email, slack, etc.)
    fi

    # Verify integrity
    if ! tar -tzf "$LATEST_BACKUP" >/dev/null 2>&1; then
        echo "ERROR: Latest backup is corrupted"
        # Send critical alert
    fi
else
    echo "ERROR: No backups found"
    # Send critical alert
fi
```

### Monitoring Integration

- **Log aggregation**: Forward backup logs to monitoring system
- **Alerting**: Set up alerts for backup failures
- **Metrics**: Track backup size, duration, success rate
- **Dashboard**: Create backup status dashboard

## Security Considerations

### Backup Encryption

For sensitive environments, encrypt backups:

```bash
# Modify backup script to use encryption
gpg --symmetric --cipher-algo AES256 memorial_backup_20241201_120000.tar.gz
```

### Access Control

- Backup files should only be readable by `memorial` user
- Remote storage should use strong authentication
- Backup encryption keys should be stored securely
- Regular access review for backup systems

### Compliance

- Document retention policies
- Ensure backups meet regulatory requirements
- Regular security audits of backup procedures
- Test restore procedures under audit conditions
