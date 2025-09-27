# Memorial Website - Deployment Package

This directory contains all the server configuration files and documentation needed to deploy the Memorial Website to production.

## Quick Deployment Guide

1. **Server Setup**
   ```bash
   # Follow the comprehensive guide
   cat docs/server-setup.md
   ```

2. **Configure Nginx**
   ```bash
   sudo cp nginx/memorial.conf /etc/nginx/sites-available/memorial
   sudo ln -s /etc/nginx/sites-available/memorial /etc/nginx/sites-enabled/
   ```

3. **Setup Queue Workers**
   ```bash
   sudo cp systemd/memorial-worker.service /etc/systemd/system/
   sudo systemctl enable memorial-worker
   sudo systemctl start memorial-worker
   ```

4. **Install Backup System**
   ```bash
   sudo cp scripts/backup.sh /usr/local/bin/memorial-backup
   sudo chmod +x /usr/local/bin/memorial-backup
   ```

5. **Configure Cron Jobs**
   ```bash
   sudo -u memorial crontab scripts/crontab-memorial
   ```

## Directory Structure

```
deployment/
├── docs/
│   ├── server-setup.md          # Complete server setup guide
│   └── disaster-recovery.md     # Backup and restore procedures
├── nginx/
│   └── memorial.conf            # Nginx virtual host configuration
├── systemd/
│   ├── memorial-worker.service  # Queue worker service
│   └── memorial-php-fpm.conf    # PHP-FPM pool configuration
├── scripts/
│   ├── backup.sh               # Comprehensive backup script
│   ├── health-check.sh         # System health monitoring
│   ├── verify-backups.sh       # Backup verification
│   └── crontab-memorial        # Cron job configuration
└── README.md                   # This file
```

## Key Features

### Production-Ready Configuration
- **Nginx**: Optimized with SSL, rate limiting, security headers
- **PHP-FPM**: Dedicated pool with performance tuning
- **Queue Workers**: Systemd service with auto-restart
- **Security**: File permissions, rate limiting, access controls

### Backup & Recovery
- **Automated Backups**: Daily SQLite and file backups
- **Remote Storage**: rclone integration for offsite backups
- **Verification**: Automated backup integrity checking
- **Recovery**: Complete disaster recovery procedures

### Monitoring & Maintenance
- **Health Checks**: Automated system monitoring
- **Log Management**: Automated cleanup and rotation
- **Performance**: Database optimization and cache clearing
- **SSL**: Automated certificate renewal

### Security Features
- **Rate Limiting**: Protects against abuse
- **File Upload Security**: Prevents malicious uploads
- **Access Control**: Admin token authentication
- **Security Headers**: XSS, CSRF, clickjacking protection

## System Requirements

- **OS**: Ubuntu 22.04 LTS (recommended)
- **RAM**: Minimum 1GB, recommended 2GB
- **Storage**: Minimum 10GB, recommended 20GB+
- **Network**: HTTPS-capable domain name
- **Dependencies**: PHP 8.3, Nginx, SQLite3, Node.js

## Post-Deployment

After deployment, verify:

1. **Website Access**: https://your-domain.com
2. **Admin Panel**: https://your-domain.com/admin/wishes?token=your-token
3. **Queue Processing**: `sudo systemctl status memorial-worker`
4. **Backups**: `/usr/local/bin/memorial-backup list`
5. **Health Check**: `/home/memorial/deployment/scripts/health-check.sh`

## Support

For issues:
1. Check application logs: `/home/memorial/storage/logs/laravel.log`
2. Check system logs: `sudo journalctl -u memorial-worker`
3. Run health check: `/home/memorial/deployment/scripts/health-check.sh`
4. Review documentation in `docs/` directory

## Customization

Before deployment, update these placeholders:
- `your-domain.com` → Your actual domain
- `your-admin-token` → Secure admin token
- `admin@your-domain.com` → Your email address
- `remote:memorial-backups` → Your rclone remote name
