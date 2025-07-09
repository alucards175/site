# 🚀 Installation Guide - Hitmanki Store

Complete step-by-step installation guide for the case opening website.

## 📋 Prerequisites

### System Requirements
- **OS**: Linux (Ubuntu 20.04+ recommended), macOS, or Windows with WSL2
- **RAM**: Minimum 4GB, 8GB+ recommended
- **Storage**: 20GB+ free space
- **Network**: Stable internet connection

### Required Software
- **Docker**: 24.0+
- **Docker Compose**: 2.0+
- **Git**: Latest version
- **Node.js**: 18.0+ (for local development)
- **PHP**: 8.1+ (for local development)
- **Composer**: Latest version (for local development)

## 🔧 Quick Installation (Docker)

### 1. Clone Repository
```bash
git clone https://github.com/yourusername/hitmanki-store.git
cd hitmanki-store
```

### 2. Environment Setup
```bash
# Copy environment template
cp .env.example .env

# Edit environment variables
nano .env
```

### 3. Configure Environment Variables
```bash
# Application
APP_NAME="Hitmanki Store"
APP_ENV=production
APP_URL=https://hitmanki.store

# Database
DB_HOST=mysql
DB_DATABASE=hitmanki_store
DB_USERNAME=hitmanki
DB_PASSWORD=your_secure_password

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=your_redis_password

# Steam API
STEAM_API_KEY=your_steam_api_key

# Payment Gateways
QIWI_PUBLIC_KEY=your_qiwi_key
QIWI_SECRET_KEY=your_qiwi_secret
YOOMONEY_CLIENT_ID=your_yoomoney_client_id
CRYPTO_BOT_TOKEN=your_crypto_bot_token

# Telegram
TELEGRAM_BOT_TOKEN=your_telegram_bot_token
TELEGRAM_ADMIN_CHAT_ID=your_admin_chat_id
```

### 4. Start Services
```bash
# Start all services
docker-compose up -d

# Check service status
docker-compose ps
```

### 5. Initialize Database
```bash
# Enter Laravel container
docker-compose exec backend bash

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed database with sample data
php artisan db:seed

# Exit container
exit
```

### 6. Verify Installation
```bash
# Check application health
curl http://localhost:8000/health

# Check WebSocket server
curl http://localhost:3001/health

# Check frontend
curl http://localhost:3000
```

## 🔧 Development Installation

### 1. Backend Setup (Laravel)
```bash
cd backend

# Install dependencies
composer install

# Copy environment
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure database connection in .env
# Then run migrations
php artisan migrate --seed

# Start development server
php artisan serve --host=0.0.0.0 --port=8000
```

### 2. WebSocket Server Setup
```bash
cd websocket

# Install dependencies
npm install

# Copy environment
cp .env.example .env

# Start development server
npm run dev
```

### 3. Frontend Setup (Vue.js)
```bash
cd frontend

# Install dependencies
npm install

# Copy environment
cp .env.example .env

# Start development server
npm run dev
```

## 🗄️ Database Configuration

### MySQL Setup
```sql
-- Create database
CREATE DATABASE hitmanki_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Create user
CREATE USER 'hitmanki'@'%' IDENTIFIED BY 'your_secure_password';

-- Grant privileges
GRANT ALL PRIVILEGES ON hitmanki_store.* TO 'hitmanki'@'%';
FLUSH PRIVILEGES;
```

### Redis Configuration
```bash
# Redis configuration in redis.conf
bind 127.0.0.1
port 6379
requirepass your_redis_password
maxmemory 256mb
maxmemory-policy allkeys-lru
```

## 🔑 API Keys Setup

### Steam API Key
1. Visit [Steam API Key Registration](https://steamcommunity.com/dev/apikey)
2. Register your domain
3. Copy API key to `STEAM_API_KEY` in `.env`

### Payment Gateway Setup

#### Qiwi Wallet
1. Register at [Qiwi Developer](https://developer.qiwi.com/)
2. Create payment project
3. Get public and secret keys
4. Configure webhook URL: `https://yourdomain.com/api/webhooks/payment/qiwi`

#### YooMoney
1. Register at [YooMoney for Developers](https://yoomoney.ru/developers)
2. Create application
3. Get client ID and secret
4. Configure webhook URL: `https://yourdomain.com/api/webhooks/payment/yoomoney`

#### Crypto Bot
1. Contact [@CryptoBot](https://t.me/CryptoBot) on Telegram
2. Create payment app
3. Get bot token
4. Configure webhook URL: `https://yourdomain.com/api/webhooks/payment/crypto`

### Telegram Bot Setup
1. Create bot via [@BotFather](https://t.me/BotFather)
2. Get bot token
3. Add bot to admin chat
4. Get chat ID using `https://api.telegram.org/bot<token>/getUpdates`

## 🌐 Production Deployment

### Domain and SSL Setup
```bash
# Install Certbot
sudo apt-get install certbot python3-certbot-nginx

# Get SSL certificate
sudo certbot --nginx -d hitmanki.store -d www.hitmanki.store

# Auto-renewal
sudo systemctl enable certbot.timer
```

### Nginx Configuration
```nginx
server {
    listen 80;
    listen [::]:80;
    server_name hitmanki.store www.hitmanki.store;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name hitmanki.store www.hitmanki.store;

    ssl_certificate /etc/letsencrypt/live/hitmanki.store/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/hitmanki.store/privkey.pem;

    # Frontend
    location / {
        proxy_pass http://localhost:3000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }

    # API
    location /api {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }

    # WebSocket
    location /socket.io {
        proxy_pass http://localhost:3001;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
    }
}
```

### Process Management (PM2)
```bash
# Install PM2
npm install -g pm2

# Start WebSocket server
cd websocket
pm2 start ecosystem.config.js

# Start Laravel worker
cd backend
pm2 start "php artisan queue:work --sleep=3 --tries=3" --name="laravel-worker"

# Start Laravel Horizon
pm2 start "php artisan horizon" --name="laravel-horizon"

# Save PM2 configuration
pm2 save
pm2 startup
```

## 📊 Monitoring Setup

### Application Monitoring
```bash
# Install monitoring tools
npm install -g @pm2/pm2-plus-node-agent

# Configure PM2 monitoring
pm2 install pm2-server-monit
```

### Database Monitoring
```bash
# Enable MySQL slow query log
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
```

### Log Management
```bash
# Configure log rotation
sudo nano /etc/logrotate.d/hitmanki

# Content:
/var/log/hitmanki/*.log {
    daily
    missingok
    rotate 30
    compress
    notifempty
    create 0644 www-data www-data
    postrotate
        systemctl reload nginx
    endscript
}
```

## 🔒 Security Hardening

### Firewall Configuration
```bash
# Enable UFW
sudo ufw enable

# Allow necessary ports
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS

# Deny all other incoming
sudo ufw default deny incoming
sudo ufw default allow outgoing
```

### Fail2Ban Setup
```bash
# Install Fail2Ban
sudo apt-get install fail2ban

# Configure
sudo nano /etc/fail2ban/jail.local

# Content:
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 5

[sshd]
enabled = true

[nginx-http-auth]
enabled = true

[nginx-limit-req]
enabled = true
```

## 🧪 Testing Installation

### Run Test Suite
```bash
# Backend tests
cd backend
php artisan test

# Frontend tests
cd frontend
npm run test

# WebSocket tests
cd websocket
npm test
```

### Load Testing
```bash
# Install Artillery
npm install -g artillery

# Run load test
artillery quick --count 10 --num 3 http://localhost:8000/api/v1/cases
```

## 🚨 Troubleshooting

### Common Issues

#### Database Connection Failed
```bash
# Check MySQL status
docker-compose logs mysql

# Reset database
docker-compose down -v
docker-compose up -d mysql
```

#### WebSocket Connection Issues
```bash
# Check WebSocket logs
docker-compose logs websocket

# Verify Redis connection
docker-compose exec redis redis-cli ping
```

#### Permission Issues
```bash
# Fix Laravel permissions
sudo chown -R www-data:www-data backend/storage
sudo chmod -R 755 backend/storage
```

### Performance Issues
```bash
# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📞 Support

### Getting Help
- **Documentation**: Check `/docs` folder
- **Issues**: GitHub Issues
- **Discord**: Join our Discord server
- **Email**: support@hitmanki.store

### Reporting Bugs
1. Check existing issues
2. Provide detailed description
3. Include system information
4. Add reproduction steps
5. Attach relevant logs

---

## ✅ Final Checklist

Before going live, ensure:

- [ ] All environment variables configured
- [ ] SSL certificate installed
- [ ] Database properly seeded
- [ ] Payment gateways tested
- [ ] Steam integration working
- [ ] WebSocket connections stable
- [ ] Monitoring configured
- [ ] Backups scheduled
- [ ] Security measures active
- [ ] Performance optimized

**Congratulations! Your case opening website is ready to launch! 🎉**