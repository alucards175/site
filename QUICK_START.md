# 🚀 Быстрый запуск Hitmanki.store

## ✅ Проблема решена!

Все недостающие Docker файлы созданы:

- ✅ `backend/Dockerfile.horizon` - для Laravel Horizon (очереди)
- ✅ `backend/Dockerfile.scheduler` - для Laravel Scheduler (cron задачи)
- ✅ `websocket/Dockerfile` - для Node.js WebSocket сервера
- ✅ `frontend/Dockerfile` - для Vue.js приложения
- ✅ `frontend/nginx.conf` - конфигурация nginx для SPA
- ✅ `nginx/nginx.conf` - основная конфигурация nginx
- ✅ `nginx/sites-available/hitmanki.store.conf` - конфигурация сайта

## 🏃‍♂️ Команды для запуска

### 1. Установка Docker (если не установлен)
```bash
# Ubuntu/Debian
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Или через package manager
sudo apt-get update
sudo apt-get install docker.io docker-compose-plugin
```

### 2. Запуск проекта
```bash
# Сборка всех образов
docker compose build

# Запуск всех сервисов
docker compose up -d

# Проверка статуса сервисов
docker compose ps
```

### 3. Инициализация базы данных
```bash
# Вход в контейнер Laravel
docker compose exec backend bash

# Генерация ключа приложения
php artisan key:generate

# Запуск миграций
php artisan migrate --seed

# Выход из контейнера
exit
```

### 4. Доступ к приложению
- **Frontend**: http://localhost:3000
- **Backend API**: http://localhost:8000
- **WebSocket**: ws://localhost:3001
- **Админ панель**: http://localhost:8000/admin

## 🔧 Полезные команды

### Просмотр логов
```bash
# Все сервисы
docker compose logs -f

# Конкретный сервис
docker compose logs -f backend
docker compose logs -f websocket
docker compose logs -f frontend
```

### Остановка и очистка
```bash
# Остановка всех сервисов
docker compose down

# Остановка с удалением volumes
docker compose down -v

# Пересборка конкретного сервиса
docker compose build backend
docker compose up -d backend
```

### Обновление кода
```bash
# При изменении backend кода
docker compose restart backend horizon scheduler

# При изменении frontend кода
docker compose build frontend
docker compose up -d frontend

# При изменении websocket кода
docker compose restart websocket
```

## 🐛 Устранение неполадок

### Ошибка портов
Если порты заняты, измените их в `docker-compose.yml`:
```yaml
ports:
  - "8080:8000"  # вместо 8000:8000
  - "3001:3000"  # вместо 3000:3000
```

### Ошибка прав доступа
```bash
# Исправление прав для Laravel
sudo chown -R $USER:$USER backend/storage
chmod -R 755 backend/storage
```

### Ошибка базы данных
```bash
# Пересоздание базы данных
docker compose down mysql
docker volume rm hitmanki-store_mysql_data
docker compose up -d mysql
# Подождать 30 секунд, затем запустить миграции
```

## 🎉 Готово!

Ваш сайт для открытия кейсов hitmanki.store готов к работе!

Проект включает:
- ✅ Laravel API с честной системой Provably Fair
- ✅ Node.js WebSocket сервер для реального времени
- ✅ Vue.js SPA с современным интерфейсом
- ✅ MySQL база данных
- ✅ Redis для кеширования и сессий
- ✅ Nginx как reverse proxy
- ✅ Laravel Horizon для управления очередями
- ✅ Laravel Scheduler для cron задач
- ✅ Полная интеграция Steam
- ✅ Множественные платежные шлюзы
- ✅ Система безопасности enterprise уровня

**Удачи в запуске вашего gambling проекта! 🚀🎰**