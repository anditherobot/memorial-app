#!/bin/bash

# Memorial Website Backup Script
# Place at: /usr/local/bin/memorial-backup
# Make executable: chmod +x /usr/local/bin/memorial-backup

set -euo pipefail

# Configuration
APP_DIR="/home/memorial/app"
BACKUP_DIR="/home/memorial/backups"
RETENTION_DAYS=30
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_NAME="memorial_backup_${TIMESTAMP}"
RCLONE_REMOTE="remote:memorial-backups"  # Configure with: rclone config
LOG_FILE="/var/log/memorial-backup.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

error() {
    log "${RED}ERROR: $1${NC}"
    exit 1
}

success() {
    log "${GREEN}SUCCESS: $1${NC}"
}

warning() {
    log "${YELLOW}WARNING: $1${NC}"
}

# Check if running as memorial user
if [[ $EUID -eq 0 ]]; then
    error "This script should not be run as root. Run as 'memorial' user."
fi

if [[ $(whoami) != "memorial" ]]; then
    error "This script must be run as the 'memorial' user."
fi

# Create backup directory
mkdir -p "$BACKUP_DIR"
cd "$BACKUP_DIR"

case "${1:-backup}" in
    "backup")
        log "Starting Memorial Website backup..."

        # Create temporary directory for this backup
        TEMP_DIR=$(mktemp -d)
        BACKUP_PATH="$TEMP_DIR/$BACKUP_NAME"
        mkdir -p "$BACKUP_PATH"

        # Function to cleanup on exit
        cleanup() {
            rm -rf "$TEMP_DIR"
        }
        trap cleanup EXIT

        # 1. Database backup
        log "Backing up SQLite database..."
        if [[ -f "$APP_DIR/database/database.sqlite" ]]; then
            sqlite3 "$APP_DIR/database/database.sqlite" ".backup '$BACKUP_PATH/database.sqlite'"
            success "Database backup completed"
        else
            error "Database file not found at $APP_DIR/database/database.sqlite"
        fi

        # 2. Storage directory (uploaded files)
        log "Backing up storage directory..."
        if [[ -d "$APP_DIR/storage" ]]; then
            cp -r "$APP_DIR/storage" "$BACKUP_PATH/"
            success "Storage backup completed"
        else
            warning "Storage directory not found"
        fi

        # 3. Environment configuration
        log "Backing up configuration..."
        if [[ -f "$APP_DIR/.env" ]]; then
            cp "$APP_DIR/.env" "$BACKUP_PATH/env.backup"
            success "Environment configuration backed up"
        else
            warning ".env file not found"
        fi

        # 4. Create backup metadata
        cat > "$BACKUP_PATH/backup_info.txt" << EOF
Memorial Website Backup
=======================
Backup Date: $(date)
Backup Type: Full
Application Path: $APP_DIR
Database: SQLite
Server: $(hostname)
Laravel Version: $(cd "$APP_DIR" && php artisan --version 2>/dev/null || echo "Unknown")

Contents:
- database.sqlite: SQLite database dump
- storage/: Uploaded files and logs
- env.backup: Environment configuration
EOF

        # 5. Create compressed archive
        log "Creating compressed archive..."
        cd "$TEMP_DIR"
        tar -czf "$BACKUP_DIR/${BACKUP_NAME}.tar.gz" "$BACKUP_NAME"
        success "Backup archive created: ${BACKUP_NAME}.tar.gz"

        # 6. Upload to remote storage (if configured)
        if command -v rclone >/dev/null 2>&1; then
            log "Uploading backup to remote storage..."
            if rclone ls "$RCLONE_REMOTE" >/dev/null 2>&1; then
                rclone copy "$BACKUP_DIR/${BACKUP_NAME}.tar.gz" "$RCLONE_REMOTE/" --progress
                success "Backup uploaded to remote storage"
            else
                warning "Remote storage not configured or accessible"
            fi
        else
            warning "rclone not installed, skipping remote upload"
        fi

        # 7. Cleanup old backups
        log "Cleaning up old backups (older than $RETENTION_DAYS days)..."
        find "$BACKUP_DIR" -name "memorial_backup_*.tar.gz" -mtime +$RETENTION_DAYS -delete
        success "Old backups cleaned up"

        # 8. Verify backup
        log "Verifying backup integrity..."
        if tar -tzf "$BACKUP_DIR/${BACKUP_NAME}.tar.gz" >/dev/null 2>&1; then
            success "Backup verification passed"
        else
            error "Backup verification failed"
        fi

        # 9. Display backup info
        BACKUP_SIZE=$(du -h "$BACKUP_DIR/${BACKUP_NAME}.tar.gz" | cut -f1)
        success "Backup completed successfully!"
        log "Backup file: ${BACKUP_NAME}.tar.gz"
        log "Backup size: $BACKUP_SIZE"
        log "Location: $BACKUP_DIR"
        ;;

    "restore")
        if [[ -z "${2:-}" ]]; then
            error "Usage: $0 restore <backup_file.tar.gz>"
        fi

        RESTORE_FILE="$2"
        if [[ ! -f "$RESTORE_FILE" ]]; then
            error "Backup file not found: $RESTORE_FILE"
        fi

        log "Starting restore from: $RESTORE_FILE"

        # Create temporary directory for restore
        TEMP_DIR=$(mktemp -d)
        cleanup() {
            rm -rf "$TEMP_DIR"
        }
        trap cleanup EXIT

        # Extract backup
        log "Extracting backup archive..."
        cd "$TEMP_DIR"
        tar -xzf "$RESTORE_FILE"

        # Find backup directory
        RESTORE_DIR=$(find . -name "memorial_backup_*" -type d | head -1)
        if [[ -z "$RESTORE_DIR" ]]; then
            error "Invalid backup archive structure"
        fi

        cd "$RESTORE_DIR"

        # Confirm restore
        echo -e "${YELLOW}WARNING: This will overwrite the current application data!${NC}"
        echo -e "Application directory: $APP_DIR"
        echo -e "Backup contains:"
        cat backup_info.txt 2>/dev/null || echo "No backup info available"
        echo
        read -p "Are you sure you want to continue? (yes/no): " -r
        if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
            log "Restore cancelled by user"
            exit 0
        fi

        # Stop services
        log "Stopping services..."
        sudo systemctl stop memorial-worker || warning "Could not stop worker service"

        # Restore database
        if [[ -f "database.sqlite" ]]; then
            log "Restoring database..."
            cp "database.sqlite" "$APP_DIR/database/database.sqlite"
            chmod 664 "$APP_DIR/database/database.sqlite"
            success "Database restored"
        else
            warning "No database backup found"
        fi

        # Restore storage
        if [[ -d "storage" ]]; then
            log "Restoring storage..."
            rm -rf "$APP_DIR/storage"
            cp -r "storage" "$APP_DIR/"
            sudo chown -R memorial:www-data "$APP_DIR/storage"
            sudo chmod -R 775 "$APP_DIR/storage"
            success "Storage restored"
        else
            warning "No storage backup found"
        fi

        # Restore environment (optional)
        if [[ -f "env.backup" ]]; then
            echo -e "${YELLOW}Environment configuration found in backup.${NC}"
            read -p "Do you want to restore .env file? (yes/no): " -r
            if [[ $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
                cp "env.backup" "$APP_DIR/.env"
                chmod 600 "$APP_DIR/.env"
                log "Environment configuration restored"
            fi
        fi

        # Clear caches
        log "Clearing application caches..."
        cd "$APP_DIR"
        php artisan config:clear
        php artisan cache:clear
        php artisan view:clear
        php artisan route:clear

        # Start services
        log "Starting services..."
        sudo systemctl start memorial-worker || warning "Could not start worker service"

        success "Restore completed successfully!"
        ;;

    "list")
        log "Available backups:"
        if [[ -d "$BACKUP_DIR" ]]; then
            ls -lh "$BACKUP_DIR"/memorial_backup_*.tar.gz 2>/dev/null || log "No backups found"
        else
            log "Backup directory does not exist"
        fi
        ;;

    "verify")
        if [[ -z "${2:-}" ]]; then
            error "Usage: $0 verify <backup_file.tar.gz>"
        fi

        VERIFY_FILE="$2"
        if [[ ! -f "$VERIFY_FILE" ]]; then
            error "Backup file not found: $VERIFY_FILE"
        fi

        log "Verifying backup: $VERIFY_FILE"

        # Test archive integrity
        if tar -tzf "$VERIFY_FILE" >/dev/null 2>&1; then
            success "Archive integrity: OK"
        else
            error "Archive integrity: FAILED"
        fi

        # Extract and check contents
        TEMP_DIR=$(mktemp -d)
        cleanup() {
            rm -rf "$TEMP_DIR"
        }
        trap cleanup EXIT

        cd "$TEMP_DIR"
        tar -xzf "$VERIFY_FILE"

        BACKUP_DIR_NAME=$(find . -name "memorial_backup_*" -type d | head -1)
        if [[ -n "$BACKUP_DIR_NAME" ]]; then
            cd "$BACKUP_DIR_NAME"

            [[ -f "database.sqlite" ]] && log "✓ Database backup present" || warning "✗ Database backup missing"
            [[ -d "storage" ]] && log "✓ Storage backup present" || warning "✗ Storage backup missing"
            [[ -f "env.backup" ]] && log "✓ Environment backup present" || warning "✗ Environment backup missing"
            [[ -f "backup_info.txt" ]] && log "✓ Backup metadata present" || warning "✗ Backup metadata missing"

            if [[ -f "backup_info.txt" ]]; then
                echo
                cat backup_info.txt
            fi
        else
            error "Invalid backup structure"
        fi
        ;;

    *)
        echo "Memorial Website Backup Script"
        echo "Usage: $0 {backup|restore|list|verify} [options]"
        echo
        echo "Commands:"
        echo "  backup                     Create a new backup"
        echo "  restore <backup_file>      Restore from backup file"
        echo "  list                       List available backups"
        echo "  verify <backup_file>       Verify backup integrity"
        echo
        echo "Examples:"
        echo "  $0 backup"
        echo "  $0 restore memorial_backup_20241201_120000.tar.gz"
        echo "  $0 verify memorial_backup_20241201_120000.tar.gz"
        ;;
esac