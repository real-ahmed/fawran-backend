# Fawran Backend - Server Deployment Guide

This document outlines the standard procedure for deploying the Fawran Marketplace Backend (Laravel 11) to a production Linux server (Ubuntu 22.04/24.04 recommended).

---

## 1. Prerequisites

Ensure your server has the following software installed:
- **PHP 8.2+** (PHP 8.4 recommended based on Laravel Boost guidelines)
- **Composer** (v2.x)
- **MySQL 8.0+** or **PostgreSQL 14+**
- **Nginx** or **Apache**
- **Redis** (Highly recommended for caching and queues)
- **Supervisor** (For managing background queue workers)
- **Git**

Required PHP Extensions:
`bcmath`, `ctype`, `fileinfo`, `json`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `cURL`, `GD/Imagick`.

---

## 2. Server Provisioning & Code Checkout

1. SSH into your server and navigate to your web root (e.g., `/var/www/`).
2. Clone the repository:
   ```bash
   git clone https://github.com/your-repo/fawran-backend.git fawran
   cd fawran
   ```

---

## 3. Environment Configuration

1. Copy the example environment file:
   ```bash
   cp .env.example .env
   ```
2. Open `.env` and configure your production credentials:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://api.fawran.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=fawran_prod
   DB_USERNAME=your_db_user
   DB_PASSWORD=your_db_pass
   
   # If using Redis
   CACHE_STORE=redis
   QUEUE_CONNECTION=redis
   SESSION_DRIVER=redis
   ```

---

## 4. Install Dependencies

Install the PHP dependencies. The `--no-dev` flag ensures testing/debugging tools aren't installed, and `--optimize-autoloader` speeds up class loading.

```bash
composer install --optimize-autoloader --no-dev
```

---

## 5. Generate Keys & Link Storage

Laravel requires an application key, and since we are using JWT, we also need a JWT secret.

```bash
# Generate the main application key
php artisan key:generate --force

# Generate the JWT Auth secret
php artisan jwt:secret --force

# Link the storage directory so uploaded media is publicly accessible
php artisan storage:link
```

---

## 6. Database Migration

Run the database migrations to build the strict Zero-Null schema.

```bash
php artisan migrate --force

# If you have initial seeders (like Admin credentials or System Settings), run:
# php artisan db:seed --force
```

---

## 7. Directory Permissions

Ensure the web server (usually `www-data` on Ubuntu) has write access to the necessary directories.

```bash
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

---

## 8. Application Optimization

Run Laravel's optimization commands to heavily cache configuration, routes, and views. This significantly improves API response times.

```bash
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

*(Note: Whenever you deploy new code or change the `.env` file, you must re-run `php artisan optimize:clear` and `php artisan optimize`.)*

---

## 9. Configuring Queues (Supervisor)

Fawran relies on background jobs for notifications, order assignments, push notifications, and geofencing calculations. Install Supervisor to keep the queue worker running permanently.

Notification delivery is queued through Laravel notifications. Current notification classes use `App\Notifications\Concerns\QueuesNotificationDelivery`, which sends `mail`, `database`, `broadcast`, SMS, and FCM notification jobs to the dedicated `notifications` queue. Keep `notifications` before `default` in the worker queue list so user-facing notifications are processed first.

1. Create a configuration file: `sudo nano /etc/supervisor/conf.d/fawran-worker.conf`
2. Add the following configuration:
   ```ini
   [program:fawran-worker]
   process_name=%(program_name)s_%(process_num)02d
   command=php /var/www/fawran/artisan queue:work --queue=notifications,default --sleep=3 --tries=3 --max-time=3600
   autostart=true
   autorestart=true
   stopasgroup=true
   killasgroup=true
   user=www-data
   numprocs=4
   redirect_stderr=true
   stdout_logfile=/var/www/fawran/storage/logs/worker.log
   stopwaitsecs=3600
   ```
3. Start the workers:
   ```bash
   sudo supervisorctl reread
   sudo supervisorctl update
   sudo supervisorctl start fawran-worker:*
   ```

Useful queue commands:

```bash
# Process notification jobs locally during development
php artisan queue:work --queue=notifications,default

# Pause and resume notification jobs on the database queue connection
php artisan queue:pause database:notifications
php artisan queue:continue database:notifications
```

---

## 10. Web Server Configuration (Nginx Example)

Create an Nginx server block to serve the application: `sudo nano /etc/nginx/sites-available/fawran`

```nginx
server {
    listen 80;
    server_name api.fawran.com;
    root /var/www/fawran/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.4-fpm.sock; # Adjust PHP version
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable the site and restart Nginx:
```bash
sudo ln -s /etc/nginx/sites-available/fawran /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## Deployment Complete! 🎉
The Fawran API is now securely running on your server, optimized, and ready to receive requests.
