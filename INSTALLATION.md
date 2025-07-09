# 🚀 Инструкция по установке - Hitmanki Store

Пошаговое руководство по установке сайта для открытия кейсов.

## 📋 Требования

### Системные требования
- **ОС**: Linux (рекомендуется Ubuntu 20.04+), macOS, или Windows с WSL2
- **ОЗУ**: Минимум 4ГБ, рекомендуется 8ГБ+
- **Хранилище**: 20ГБ+ свободного места
- **Сеть**: Стабильное интернет-соединение

### Необходимое ПО
- **Docker**: 24.0+
- **Docker Compose**: 2.0+
- **Git**: Последняя версия
- **Node.js**: 18.0+ (для локальной разработки)
- **PHP**: 8.1+ (для локальной разработки)
- **Composer**: Последняя версия (для локальной разработки)

## 🔧 Быстрая установка (Docker)

### 1. Клонирование репозитория
```bash
git clone https://github.com/yourusername/hitmanki-store.git
cd hitmanki-store
```

### 2. Настройка окружения
```bash
# Копируем шаблон окружения
cp .env.example .env

# Редактируем переменные окружения
nano .env
```

### 3. Настройка переменных окружения
```bash
# Приложение
APP_NAME="Hitmanki Store"
APP_ENV=production
APP_URL=https://hitmanki.store

# База данных
DB_HOST=mysql
DB_DATABASE=hitmanki_store
DB_USERNAME=hitmanki
DB_PASSWORD=ваш_безопасный_пароль

# Redis
REDIS_HOST=redis
REDIS_PASSWORD=ваш_redis_пароль

# Steam API
STEAM_API_KEY=ваш_steam_api_ключ

# Платежные шлюзы
QIWI_PUBLIC_KEY=ваш_qiwi_ключ
QIWI_SECRET_KEY=ваш_qiwi_секрет
YOOMONEY_CLIENT_ID=ваш_yoomoney_client_id
CRYPTO_BOT_TOKEN=ваш_crypto_bot_токен

# Telegram
TELEGRAM_BOT_TOKEN=ваш_telegram_bot_токен
TELEGRAM_ADMIN_CHAT_ID=ваш_admin_chat_id
```

### 4. Запуск сервисов
```bash
# Запускаем все сервисы
docker-compose up -d

# Проверяем статус сервисов
docker-compose ps
```

### 5. Инициализация базы данных
```bash
# Входим в контейнер Laravel
docker-compose exec backend bash

# Генерируем ключ приложения
php artisan key:generate

# Запускаем миграции
php artisan migrate

# Наполняем базу тестовыми данными
php artisan db:seed

# Выходим из контейнера
exit
```

### 6. Проверка установки
```bash
# Проверяем здоровье приложения
curl http://localhost:8000/health

# Проверяем WebSocket сервер
curl http://localhost:3001/health

# Проверяем фронтенд
curl http://localhost:3000
```

## 🔧 Установка для разработки

### 1. Настройка Backend (Laravel)
```bash
cd backend

# Устанавливаем зависимости
composer install

# Копируем окружение
cp .env.example .env

# Генерируем ключ приложения
php artisan key:generate

# Настраиваем подключение к БД в .env
# Затем запускаем миграции
php artisan migrate --seed

# Запускаем сервер разработки
php artisan serve --host=0.0.0.0 --port=8000
```

### 2. Настройка WebSocket сервера
```bash
cd websocket

# Устанавливаем зависимости
npm install

# Копируем окружение
cp .env.example .env

# Запускаем сервер разработки
npm run dev
```

### 3. Настройка Frontend (Vue.js)
```bash
cd frontend

# Устанавливаем зависимости
npm install

# Копируем окружение
cp .env.example .env

# Запускаем сервер разработки
npm run dev
```

## 🗄️ Настройка базы данных

### Настройка MySQL
```sql
-- Создаем базу данных
CREATE DATABASE hitmanki_store CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Создаем пользователя
CREATE USER 'hitmanki'@'%' IDENTIFIED BY 'ваш_безопасный_пароль';

-- Предоставляем привилегии
GRANT ALL PRIVILEGES ON hitmanki_store.* TO 'hitmanki'@'%';
FLUSH PRIVILEGES;
```

### Настройка Redis
```bash
# Конфигурация Redis в redis.conf
bind 127.0.0.1
port 6379
requirepass ваш_redis_пароль
maxmemory 256mb
maxmemory-policy allkeys-lru
```

## 🔑 Настройка API ключей

### Steam API ключ
1. Посетите [Регистрация Steam API ключа](https://steamcommunity.com/dev/apikey)
2. Зарегистрируйте ваш домен
3. Скопируйте API ключ в `STEAM_API_KEY` в `.env`

### Настройка платежных шлюзов

#### Qiwi Wallet
1. Зарегистрируйтесь на [Qiwi Developer](https://developer.qiwi.com/)
2. Создайте платежный проект
3. Получите публичный и секретный ключи
4. Настройте webhook URL: `https://вашдомен.com/api/webhooks/payment/qiwi`

#### ЮMoney
1. Зарегистрируйтесь на [ЮMoney для разработчиков](https://yoomoney.ru/developers)
2. Создайте приложение
3. Получите client ID и secret
4. Настройте webhook URL: `https://вашдомен.com/api/webhooks/payment/yoomoney`

#### Crypto Bot
1. Обратитесь к [@CryptoBot](https://t.me/CryptoBot) в Telegram
2. Создайте платежное приложение
3. Получите токен бота
4. Настройте webhook URL: `https://вашдомен.com/api/webhooks/payment/crypto`

### Настройка Telegram бота
1. Создайте бота через [@BotFather](https://t.me/BotFather)
2. Получите токен бота
3. Добавьте бота в админский чат
4. Получите ID чата используя `https://api.telegram.org/bot<токен>/getUpdates`

## 🌐 Продакшн развертывание

### Настройка домена и SSL
```bash
# Устанавливаем Certbot
sudo apt-get install certbot python3-certbot-nginx

# Получаем SSL сертификат
sudo certbot --nginx -d hitmanki.store -d www.hitmanki.store

# Автообновление
sudo systemctl enable certbot.timer
```

### Конфигурация Nginx
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

    # Фронтенд
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

### Управление процессами (PM2)
```bash
# Устанавливаем PM2
npm install -g pm2

# Запускаем WebSocket сервер
cd websocket
pm2 start ecosystem.config.js

# Запускаем Laravel worker
cd backend
pm2 start "php artisan queue:work --sleep=3 --tries=3" --name="laravel-worker"

# Запускаем Laravel Horizon
pm2 start "php artisan horizon" --name="laravel-horizon"

# Сохраняем конфигурацию PM2
pm2 save
pm2 startup
```

## 📊 Настройка мониторинга

### Мониторинг приложений
```bash
# Устанавливаем инструменты мониторинга
npm install -g @pm2/pm2-plus-node-agent

# Настраиваем мониторинг PM2
pm2 install pm2-server-monit
```

### Мониторинг базы данных
```bash
# Включаем лог медленных запросов MySQL
SET GLOBAL slow_query_log = 'ON';
SET GLOBAL long_query_time = 2;
```

## 🔒 Усиление безопасности

### Настройка файервола
```bash
# Включаем UFW
sudo ufw enable

# Разрешаем необходимые порты
sudo ufw allow 22/tcp    # SSH
sudo ufw allow 80/tcp    # HTTP
sudo ufw allow 443/tcp   # HTTPS

# Запрещаем все остальные входящие
sudo ufw default deny incoming
sudo ufw default allow outgoing
```

### Настройка Fail2Ban
```bash
# Устанавливаем Fail2Ban
sudo apt-get install fail2ban

# Настраиваем
sudo nano /etc/fail2ban/jail.local

# Содержимое:
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

## 🧪 Тестирование установки

### Запуск тестов
```bash
# Тесты backend
cd backend
php artisan test

# Тесты frontend
cd frontend
npm run test

# Тесты WebSocket
cd websocket
npm test
```

### Нагрузочное тестирование
```bash
# Устанавливаем Artillery
npm install -g artillery

# Запускаем нагрузочный тест
artillery quick --count 10 --num 3 http://localhost:8000/api/v1/cases
```

## 🚨 Устранение неполадок

### Частые проблемы

#### Ошибка подключения к базе данных
```bash
# Проверяем статус MySQL
docker-compose logs mysql

# Сбрасываем базу данных
docker-compose down -v
docker-compose up -d mysql
```

#### Проблемы с WebSocket соединением
```bash
# Проверяем логи WebSocket
docker-compose logs websocket

# Проверяем подключение к Redis
docker-compose exec redis redis-cli ping
```

#### Проблемы с правами доступа
```bash
# Исправляем права Laravel
sudo chown -R www-data:www-data backend/storage
sudo chmod -R 755 backend/storage
```

### Проблемы производительности
```bash
# Очищаем все кеши
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Оптимизируем для продакшена
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 📞 Поддержка

### Получение помощи
- **Документация**: Проверьте папку `/docs`
- **Issues**: GitHub Issues
- **Discord**: Присоединяйтесь к нашему Discord серверу
- **Email**: support@hitmanki.store

### Сообщение об ошибках
1. Проверьте существующие issues
2. Предоставьте подробное описание
3. Включите информацию о системе
4. Добавьте шаги воспроизведения
5. Приложите соответствующие логи

---

## ✅ Финальный чеклист

Перед запуском убедитесь:

- [ ] Все переменные окружения настроены
- [ ] SSL сертификат установлен
- [ ] База данных правильно наполнена
- [ ] Платежные шлюзы протестированы
- [ ] Интеграция Steam работает
- [ ] WebSocket соединения стабильны
- [ ] Мониторинг настроен
- [ ] Бэкапы запланированы
- [ ] Меры безопасности активны
- [ ] Производительность оптимизирована

**Поздравляем! Ваш сайт для открытия кейсов готов к запуску! 🎉**