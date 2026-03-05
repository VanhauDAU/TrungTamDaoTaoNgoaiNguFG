# 06 — Hướng dẫn Deployment

## 1. Chuẩn bị Server (VPS/Cloud)

### Yêu cầu tối thiểu

- Ubuntu 22.04 LTS
- 2 vCPU / 2GB RAM / 20GB SSD
- PHP 8.2 + extensions: mbstring, openssl, pdo, tokenizer, xml, ctype, json, bcmath, gd
- MySQL 8.0
- Nginx hoặc Apache
- Composer 2.x, Node.js 18+, npm

---

## 2. Server Setup (Ubuntu + Nginx)

```bash
# Cài PHP 8.2
sudo apt install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt update
sudo apt install php8.2 php8.2-fpm php8.2-mysql php8.2-mbstring \
    php8.2-xml php8.2-bcmath php8.2-gd php8.2-curl php8.2-zip

# Cài MySQL
sudo apt install mysql-server
sudo mysql_secure_installation

# Cài Nginx
sudo apt install nginx

# Cài Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Cài Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs
```

---

## 3. Deploy ứng dụng

```bash
# 1. Clone vào /var/www/
cd /var/www
git clone https://github.com/VanhauDAU/TrungTamDaoTaoNgoaiNguFG.git fivegenius
cd fivegenius

# 2. Permissions
sudo chown -R www-data:www-data /var/www/fivegenius
sudo chmod -R 755 /var/www/fivegenius/storage
sudo chmod -R 755 /var/www/fivegenius/bootstrap/cache

# 3. Cài dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# 4. Cấu hình .env
cp .env.example .env
nano .env  # Cập nhật DB, APP_URL, MAIL...
php artisan key:generate

# 5. Database
php artisan migrate --force
php artisan storage:link

# 6. Cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 4. Cấu hình Nginx

Tạo file `/etc/nginx/sites-available/fivegenius`:

```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
    root /var/www/fivegenius/public;
    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/fivegenius /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

---

## 5. SSL (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
# Auto renew
sudo crontab -e
# Thêm: 0 12 * * * /usr/bin/certbot renew --quiet
```

---

## 6. Update / Deploy mới

```bash
cd /var/www/fivegenius

# Pull code mới
git pull origin main

# Cài dependencies
composer install --no-dev --optimize-autoloader
npm install && npm run build

# Chạy migration (nếu có)
php artisan migrate --force

# Clear cache
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart FPM (nếu cần)
sudo systemctl restart php8.2-fpm
```

---

## 7. Cấu hình .env Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

LOG_CHANNEL=daily
LOG_LEVEL=error

DB_HOST=127.0.0.1
DB_DATABASE=fivegenius_prod
DB_USERNAME=fivegenius_user
DB_PASSWORD=strong_password_here

FILESYSTEM_DISK=public

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
```

---

## 8. Backup Database định kỳ

```bash
# Tạo script backup
cat > /usr/local/bin/backup_db.sh << 'EOF'
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u fivegenius_user -p'strong_password' fivegenius_prod \
    > /var/backups/fivegenius_$DATE.sql
# Giữ 7 ngày gần nhất
find /var/backups/ -name "fivegenius_*.sql" -mtime +7 -delete
EOF
chmod +x /usr/local/bin/backup_db.sh

# Chạy lúc 2h sáng mỗi ngày
echo "0 2 * * * /usr/local/bin/backup_db.sh" | crontab -
```
