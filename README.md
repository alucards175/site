# Hitmanki.store - Сайт для открытия кейсов

Комплексный сайт для открытия кейсов с функциями реального времени, интеграцией Steam и честной системой Provably Fair.

## 🚀 Технологический стек

### Backend
- **Laravel 10** - API и админ-панель
- **Node.js** - WebSocket сервер (Socket.io)
- **MySQL** - Основная база данных
- **Redis** - Кеш и очереди
- **Laravel Horizon** - Мониторинг очередей
- **Laravel Sanctum** - API авторизация
- **Provably Fair** - Кастомная реализация честности

### Frontend
- **Vue.js 3** - SPA фреймворк
- **TailwindCSS** - CSS фреймворк
- **Swiper.js** - Анимации
- **Socket.io Client** - Реальное время
- **Pinia** - Управление состоянием

### DevOps
- **Nginx** - Веб-сервер и прокси
- **Docker** - Контейнеризация
- **GitHub Actions** - CI/CD
- **Let's Encrypt** - SSL сертификаты

## 📁 Структура проекта

```
hitmanki-store/
├── backend/           # Laravel API и админка
├── websocket/         # Node.js WebSocket сервер
├── frontend/          # Vue.js SPA
├── docker/           # Docker конфигурации
├── docs/             # Документация
└── nginx/            # Nginx конфигурации
```

## 🔧 Установка

### Требования
- Docker & Docker Compose
- Node.js 18+
- PHP 8.1+
- Composer

### Быстрый старт

1. **Клонировать репозиторий**
```bash
git clone https://github.com/yourusername/hitmanki-store.git
cd hitmanki-store
```

2. **Настройка окружения**
```bash
cp .env.example .env
```

3. **Запуск с Docker**
```bash
docker-compose up -d
```

4. **Установка зависимостей**
```bash
# Backend
cd backend && composer install
php artisan key:generate
php artisan migrate --seed

# Frontend
cd ../frontend && npm install

# WebSocket
cd ../websocket && npm install
```

5. **Доступ к приложению**
- Frontend: http://localhost:3000
- Админ-панель: http://localhost:8000/admin
- WebSocket: ws://localhost:3001

## 🌟 Основные функции

### Базовые возможности
- ✅ Steam OpenID авторизация
- ✅ Анимации открытия кейсов в реальном времени
- ✅ Честная система Provably Fair
- ✅ Мульти-открытие кейсов
- ✅ Авто-открытие
- ✅ Живая лента дропов
- ✅ Управление инвентарем пользователя
- ✅ Реферальная система
- ✅ Система достижений/уровней

### Интеграция платежей
- ✅ Qiwi Wallet
- ✅ ЮMoney
- ✅ Crypto Bot
- ✅ Банковские карты
- ✅ Steam Market API

### Функции админки
- ✅ Управление пользователями
- ✅ Управление кейсами и предметами
- ✅ Настройка шансов дропа
- ✅ Финансовая аналитика
- ✅ Система промокодов
- ✅ Мониторинг в реальном времени
- ✅ Антифрод система

## 📄 Лицензия

Этот проект является собственностью. Все права защищены.

## 🤝 Поддержка

По вопросам поддержки обращайтесь на support@hitmanki.store или в наш Telegram канал.