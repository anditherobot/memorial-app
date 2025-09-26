#!/bin/bash

# Memorial Website Health Check Script
# Place at: /home/memorial/deployment/scripts/health-check.sh
# Make executable: chmod +x /home/memorial/deployment/scripts/health-check.sh

set -euo pipefail

# Configuration
APP_URL="https://your-domain.com"
APP_DIR="/home/memorial/app"
LOG_FILE="/var/log/memorial-health.log"
ALERT_EMAIL="admin@your-domain.com"  # Optional: configure email alerts

# Health check results
HEALTH_STATUS="OK"
ISSUES=()

# Logging function
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

error() {
    HEALTH_STATUS="ERROR"
    ISSUES+=("$1")
    log "ERROR: $1"
}

warning() {
    if [[ "$HEALTH_STATUS" != "ERROR" ]]; then
        HEALTH_STATUS="WARNING"
    fi
    ISSUES+=("$1")
    log "WARNING: $1"
}

success() {
    log "OK: $1"
}

# Check web server response
check_web_server() {
    log "Checking web server response..."

    if command -v curl >/dev/null 2>&1; then
        response=$(curl -s -o /dev/null -w "%{http_code}" "$APP_URL" --max-time 10 || echo "000")

        if [[ "$response" == "200" ]]; then
            success "Web server responding (HTTP $response)"
        elif [[ "$response" == "000" ]]; then
            error "Web server not responding (connection failed)"
        else
            warning "Web server responding with HTTP $response"
        fi
    else
        warning "curl not available, skipping web server check"
    fi
}

# Check SSL certificate
check_ssl_certificate() {
    log "Checking SSL certificate..."

    if command -v openssl >/dev/null 2>&1; then
        domain=$(echo "$APP_URL" | sed 's|https://||' | sed 's|/.*||')

        expiry_date=$(echo | openssl s_client -servername "$domain" -connect "$domain:443" 2>/dev/null | \
                     openssl x509 -noout -enddate 2>/dev/null | cut -d= -f2)

        if [[ -n "$expiry_date" ]]; then
            expiry_epoch=$(date -d "$expiry_date" +%s 2>/dev/null || echo "0")
            current_epoch=$(date +%s)
            days_until_expiry=$(( (expiry_epoch - current_epoch) / 86400 ))

            if [[ $days_until_expiry -gt 30 ]]; then
                success "SSL certificate valid (expires in $days_until_expiry days)"
            elif [[ $days_until_expiry -gt 7 ]]; then
                warning "SSL certificate expires soon ($days_until_expiry days)"
            else
                error "SSL certificate expires very soon ($days_until_expiry days)"
            fi
        else
            warning "Could not check SSL certificate expiry"
        fi
    else
        warning "openssl not available, skipping SSL check"
    fi
}

# Check database connectivity
check_database() {
    log "Checking database connectivity..."

    cd "$APP_DIR"

    if php artisan tinker --execute="echo App\Models\User::count();" >/dev/null 2>&1; then
        success "Database connection working"
    else
        error "Database connection failed"
    fi
}

# Check queue worker
check_queue_worker() {
    log "Checking queue worker status..."

    if systemctl is-active memorial-worker >/dev/null 2>&1; then
        success "Queue worker is running"

        # Check if worker is processing jobs
        worker_log=$(journalctl -u memorial-worker --since "5 minutes ago" --no-pager -q 2>/dev/null || echo "")
        if [[ -n "$worker_log" ]]; then
            success "Queue worker is processing jobs"
        else
            warning "Queue worker running but no recent activity"
        fi
    else
        error "Queue worker is not running"
    fi
}

# Check disk space
check_disk_space() {
    log "Checking disk space..."

    disk_usage=$(df /home/memorial | awk 'NR==2 {print $5}' | sed 's/%//')

    if [[ $disk_usage -lt 80 ]]; then
        success "Disk usage OK (${disk_usage}%)"
    elif [[ $disk_usage -lt 90 ]]; then
        warning "Disk usage high (${disk_usage}%)"
    else
        error "Disk usage critical (${disk_usage}%)"
    fi
}

# Check memory usage
check_memory() {
    log "Checking memory usage..."

    memory_usage=$(free | awk 'NR==2{printf "%.0f", $3*100/$2}')

    if [[ $memory_usage -lt 80 ]]; then
        success "Memory usage OK (${memory_usage}%)"
    elif [[ $memory_usage -lt 90 ]]; then
        warning "Memory usage high (${memory_usage}%)"
    else
        error "Memory usage critical (${memory_usage}%)"
    fi
}

# Check recent errors in logs
check_error_logs() {
    log "Checking for recent errors..."

    laravel_log="/home/memorial/app/storage/logs/laravel.log"

    if [[ -f "$laravel_log" ]]; then
        recent_errors=$(grep -c "ERROR\|CRITICAL" "$laravel_log" 2>/dev/null | tail -100 || echo "0")

        if [[ $recent_errors -eq 0 ]]; then
            success "No recent errors in Laravel logs"
        elif [[ $recent_errors -lt 5 ]]; then
            warning "Few recent errors found ($recent_errors)"
        else
            error "Many recent errors found ($recent_errors)"
        fi
    else
        warning "Laravel log file not found"
    fi
}

# Check backup status
check_backup_status() {
    log "Checking backup status..."

    backup_dir="/home/memorial/backups"

    if [[ -d "$backup_dir" ]]; then
        latest_backup=$(find "$backup_dir" -name "memorial_backup_*.tar.gz" -type f -printf '%T@ %p\n' 2>/dev/null | \
                       sort -n | tail -1 | cut -d' ' -f2-)

        if [[ -n "$latest_backup" ]]; then
            backup_age=$(find "$latest_backup" -mtime +1 2>/dev/null)

            if [[ -z "$backup_age" ]]; then
                success "Recent backup found (less than 24 hours old)"
            else
                warning "Latest backup is older than 24 hours"
            fi
        else
            error "No backups found"
        fi
    else
        error "Backup directory not found"
    fi
}

# Check file permissions
check_permissions() {
    log "Checking critical file permissions..."

    # Check storage directory
    if [[ -w "$APP_DIR/storage" ]]; then
        success "Storage directory is writable"
    else
        error "Storage directory is not writable"
    fi

    # Check database file
    if [[ -w "$APP_DIR/database/database.sqlite" ]]; then
        success "Database file is writable"
    else
        error "Database file is not writable"
    fi

    # Check .env file security
    env_perms=$(stat -c "%a" "$APP_DIR/.env" 2>/dev/null || echo "000")
    if [[ "$env_perms" == "600" ]]; then
        success ".env file has secure permissions"
    else
        warning ".env file permissions are not secure ($env_perms)"
    fi
}

# Send alert if there are issues
send_alert() {
    if [[ "$HEALTH_STATUS" != "OK" && -n "$ALERT_EMAIL" ]]; then
        if command -v mail >/dev/null 2>&1; then
            {
                echo "Memorial Website Health Check Alert"
                echo "=================================="
                echo "Status: $HEALTH_STATUS"
                echo "Time: $(date)"
                echo "Server: $(hostname)"
                echo ""
                echo "Issues found:"
                printf '%s\n' "${ISSUES[@]}"
                echo ""
                echo "Please check the server immediately."
            } | mail -s "Memorial Website Health Alert - $HEALTH_STATUS" "$ALERT_EMAIL"
        fi
    fi
}

# Main execution
log "Starting health check..."

check_web_server
check_ssl_certificate
check_database
check_queue_worker
check_disk_space
check_memory
check_error_logs
check_backup_status
check_permissions

log "Health check completed - Status: $HEALTH_STATUS"

# Send alert if needed
send_alert

# Exit with appropriate code
if [[ "$HEALTH_STATUS" == "OK" ]]; then
    exit 0
elif [[ "$HEALTH_STATUS" == "WARNING" ]]; then
    exit 1
else
    exit 2
fi