# Memorial Website - Server Setup Guide

This guide covers deploying the Memorial Website on Ubuntu 22.04 LTS with Nginx, PHP-FPM, and systemd queue workers.

## Prerequisites

- Ubuntu 22.04 LTS server
- Root or sudo access
- Domain name pointed to server IP
- Minimum 1GB RAM, 10GB disk space

## 1. Initial Server Setup

### Update system packages
```bash
sudo apt update && sudo apt upgrade -y
```

### Install required packages
```bash
sudo apt install -y \
    nginx \
    php8.3-fpm \
    php8.3-cli \
    php8.3-sqlite3 \
    php8.3-gd \
    php8.3-mbstring \
    php8.3-xml \
    php8.3-curl \
    php8.3-zip \
    php8.3-intl \
    php8.3-bcmath \
    composer \
    nodejs \
    npm \
    sqlite3 \
    certbot \
    python3-certbot-nginx \
    rclone \
    jpegoptim \
    pngquant \
    optipng \
    gifsicle \
    unzip \
    git
```

### Create application user
```bash
sudo adduser --system --group --shell /bin/bash memorial
sudo usermod -aG www-data memorial
```

## 2. Application Deployment

### Clone and setup application
```bash
# Switch to memorial user
sudo su - memorial

# Create application directory
mkdir -p /home/memorial
cd /home/memorial

# Clone repository (replace with your repo URL)
git clone https://github.com/your-repo/memorial.git .
# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Install Node dependencies and build assets
npm ci
npm run build

# Set correct permissions
sudo chown -R memorial:www-data /home/memorial
sudo chmod -R 755 /home/memorial
sudo chmod -R 775 /home/memorial/storage
sudo chmod -R 775 /home/memorial/bootstrap/cache
```

### Environment configuration
```bash
# Copy environment file
cp .env.example .env

# Edit environment variables
nano .env
```

**Required .env settings:**
```env
APP_NAME="Memorial"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=sqlite
DB_DATABASE=/home/memorial/database/database.sqlite

# Generate secure keys
APP_KEY=
ADMIN_TOKEN=your-very-long-secure-admin-token
ADMIN_EMAIL=admin@your-domain.com
ADMIN_PASSWORD=your-secure-admin-password

# Mail configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-server
MAIL_PORT=587
MAIL_USERNAME=your-email
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls

# Queue configuration
QUEUE_CONNECTION=database

# Session configuration for production
SESSION_DRIVER=file
SESSION_LIFETIME=120

# Cache configuration
CACHE_STORE=file
```

### Initialize application
```bash
# Generate application key
php artisan key:generate

# Create database
mkdir -p database
touch database/database.sqlite
chmod 664 database/database.sqlite

# Run migrations and seeders
php artisan migrate --force
php artisan db:seed --force

# Create storage symlink
php artisan storage:link

# Optimize application
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 3. Web Server Configuration

### Configure Nginx
Copy the Nginx configuration from `deployment/nginx/memorial.conf` to `/etc/nginx/sites-available/memorial`

```bash
sudo cp /home/memorial/deployment/nginx/memorial.conf /etc/nginx/sites-available/memorial
sudo ln -s /etc/nginx/sites-available/memorial /etc/nginx/sites-enabled/
sudo rm /etc/nginx/sites-enabled/default
```

### Configure PHP-FPM
```bash
# Edit PHP-FPM pool configuration
sudo nano /etc/php/8.3/fpm/pool.d/memorial.conf
```

Add the memorial pool configuration from `deployment/systemd/memorial-php-fpm.conf`

### Test and reload services
```bash
# Test Nginx configuration
sudo nginx -t

# Restart services
sudo systemctl restart nginx
sudo systemctl restart php8.3-fpm
sudo systemctl enable nginx php8.3-fpm
```

## 4. SSL Certificate

### Install Let's Encrypt certificate
```bash
sudo certbot --nginx -d your-domain.com -d www.your-domain.com
```

### Auto-renewal
```bash
# Test renewal
sudo certbot renew --dry-run

# Cron job is automatically created by certbot
```

## 5. Queue Workers

### Install systemd service
```bash
sudo cp /home/memorial/deployment/systemd/memorial-worker.service /etc/systemd/system/
sudo systemctl daemon-reload
sudo systemctl enable memorial-worker
sudo systemctl start memorial-worker
```

### Monitor workers
```bash
# Check status
sudo systemctl status memorial-worker

# View logs
sudo journalctl -u memorial-worker -f
```

## 6. Backups

### Install backup script
```bash
sudo cp /home/memorial/deployment/scripts/backup.sh /usr/local/bin/memorial-backup
sudo chmod +x /usr/local/bin/memorial-backup
```

### Configure rclone (optional)
```bash
# Setup rclone remote storage
sudo -u memorial rclone config
```

### Setup cron jobs
```bash
# Edit memorial user crontab
sudo -u memorial crontab -e
```

Add the following cron entries:
```cron
# Laravel scheduler (every minute)
* * * * * cd /home/memorial/app && php artisan schedule:run >> /dev/null 2>&1

# Daily backups at 2 AM
0 2 * * * /usr/local/bin/memorial-backup

# Weekly cleanup of old logs (Sunday 3 AM)
0 3 * * 0 find /home/memorial/storage/logs -name "*.log" -mtime +30 -delete
```

## 7. Security Hardening

### Firewall configuration
```bash
# Install and configure UFW
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow 'Nginx Full'
sudo ufw enable
```

### File permissions
```bash
# Secure file permissions
sudo find /home/memorial -type f -exec chmod 644 {} \;
sudo find /home/memorial -type d -exec chmod 755 {} \;
sudo chmod -R 775 /home/memorial/storage
sudo chmod -R 775 /home/memorial/bootstrap/cache
sudo chmod 600 /home/memorial/.env
```

### Rate limiting
The application includes built-in rate limiting for uploads and forms. Monitor `/var/log/nginx/access.log` for unusual activity.

## 8. Monitoring

### Application logs
```bash
# Laravel logs
tail -f /home/memorial/storage/logs/laravel.log

# Nginx logs
tail -f /var/log/nginx/access.log
tail -f /var/log/nginx/error.log

# Queue worker logs
sudo journalctl -u memorial-worker -f
```

### Health checks
```bash
# Test application
curl -I https://your-domain.com

# Test admin access
curl -I https://your-domain.com/admin/wishes?token=your-admin-token

# Test database
cd /home/memorial && php artisan tinker
# In tinker: App\Models\User::count()
```

## 9. Maintenance

### Regular updates
```bash
# Update system packages
sudo apt update && sudo apt upgrade

# Update application
cd /home/memorial
git pull origin main
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo systemctl restart memorial-worker
```

### Backup verification
```bash
# Test backup restoration (on test server)
/usr/local/bin/memorial-backup restore backup-file.tar.gz
```

## Troubleshooting

### Common issues

**Permission denied errors:**
```bash
sudo chown -R memorial:www-data /home/memorial
sudo chmod -R 775 /home/memorial/storage
```

**Database locked errors:**
```bash
# Check for long-running processes
lsof /home/memorial/database/database.sqlite
# Kill if necessary and restart workers
```

**Queue not processing:**
```bash
sudo systemctl restart memorial-worker
sudo journalctl -u memorial-worker -n 50
```

**High memory usage:**
```bash
# Monitor PHP-FPM processes
sudo systemctl status php8.3-fpm
# Adjust pm.max_children in pool configuration if needed
```

### Performance tuning

**For high traffic:**
- Increase PHP-FPM pool size
- Enable OPcache
- Use Redis for sessions/cache
- Configure Nginx caching
- Consider CDN for static assets

**Database optimization:**
```bash
# Analyze and optimize
cd /home/memorial
sqlite3 database/database.sqlite "ANALYZE;"
sqlite3 database/database.sqlite "VACUUM;"
```
