#!/bin/bash

# Memorial Website Backup Verification Script
# Place at: /home/memorial/deployment/scripts/verify-backups.sh
# Make executable: chmod +x /home/memorial/deployment/scripts/verify-backups.sh

set -euo pipefail

# Configuration
BACKUP_DIR="/home/memorial/backups"
LOG_FILE="/var/log/memorial-backup-verify.log"
MAX_BACKUPS_TO_CHECK=5

# Logging function
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

log "Starting backup verification..."

# Find recent backups
recent_backups=$(find "$BACKUP_DIR" -name "memorial_backup_*.tar.gz" -type f -printf '%T@ %p\n' 2>/dev/null | \
                sort -nr | head -$MAX_BACKUPS_TO_CHECK | cut -d' ' -f2-)

if [[ -z "$recent_backups" ]]; then
    log "ERROR: No backups found in $BACKUP_DIR"
    exit 1
fi

verification_results=()
failed_verifications=0

while IFS= read -r backup_file; do
    log "Verifying backup: $(basename "$backup_file")"

    # Check file integrity
    if tar -tzf "$backup_file" >/dev/null 2>&1; then
        log "✓ Archive integrity OK"

        # Extract to temporary directory for content verification
        temp_dir=$(mktemp -d)
        cd "$temp_dir"

        if tar -xzf "$backup_file" >/dev/null 2>&1; then
            backup_content_dir=$(find . -name "memorial_backup_*" -type d | head -1)

            if [[ -n "$backup_content_dir" ]]; then
                cd "$backup_content_dir"

                # Check database
                if [[ -f "database.sqlite" ]]; then
                    if sqlite3 "database.sqlite" "SELECT COUNT(*) FROM sqlite_master;" >/dev/null 2>&1; then
                        log "✓ Database structure intact"
                    else
                        log "✗ Database verification failed"
                        ((failed_verifications++))
                    fi
                else
                    log "✗ Database file missing"
                    ((failed_verifications++))
                fi

                # Check storage directory
                if [[ -d "storage" ]]; then
                    log "✓ Storage directory present"
                else
                    log "✗ Storage directory missing"
                    ((failed_verifications++))
                fi

                # Check metadata
                if [[ -f "backup_info.txt" ]]; then
                    log "✓ Backup metadata present"
                else
                    log "✗ Backup metadata missing"
                    ((failed_verifications++))
                fi

                verification_results+=("$(basename "$backup_file"): OK")
            else
                log "✗ Invalid backup structure"
                verification_results+=("$(basename "$backup_file"): FAILED - Invalid structure")
                ((failed_verifications++))
            fi
        else
            log "✗ Failed to extract backup"
            verification_results+=("$(basename "$backup_file"): FAILED - Extraction error")
            ((failed_verifications++))
        fi

        # Cleanup
        rm -rf "$temp_dir"
    else
        log "✗ Archive integrity check failed"
        verification_results+=("$(basename "$backup_file"): FAILED - Corrupted archive")
        ((failed_verifications++))
    fi

    log "---"
done <<< "$recent_backups"

# Summary
log "Backup verification completed"
log "Backups checked: $(echo "$recent_backups" | wc -l)"
log "Failed verifications: $failed_verifications"

printf '%s\n' "${verification_results[@]}" | tee -a "$LOG_FILE"

if [[ $failed_verifications -eq 0 ]]; then
    log "All backup verifications passed ✓"
    exit 0
else
    log "Some backup verifications failed ✗"
    exit 1
fi