# Hitmanki.store - Case Opening Website

A comprehensive case opening website with real-time features, Steam integration, and provably fair system.

## 🚀 Tech Stack

### Backend
- **Laravel 10** - API & Admin Panel
- **Node.js** - WebSocket Server (Socket.io)
- **MySQL** - Primary Database
- **Redis** - Cache & Queues
- **Laravel Horizon** - Queue Monitoring
- **Laravel Sanctum** - API Authentication
- **Provably Fair** - Custom implementation

### Frontend
- **Vue.js 3** - SPA Framework
- **TailwindCSS** - Styling
- **Swiper.js** - Animations
- **Socket.io Client** - Real-time communication
- **Pinia** - State Management

### DevOps
- **Nginx** - Web Server & Proxy
- **Docker** - Containerization
- **GitHub Actions** - CI/CD
- **Let's Encrypt** - SSL Certificates

## 📁 Project Structure

```
hitmanki-store/
├── backend/           # Laravel API & Admin
├── websocket/         # Node.js WebSocket Server
├── frontend/          # Vue.js SPA
├── docker/           # Docker configurations
├── docs/             # Documentation
└── nginx/            # Nginx configurations
```

## 🔧 Installation

### Prerequisites
- Docker & Docker Compose
- Node.js 18+
- PHP 8.1+
- Composer

### Quick Start

1. **Clone the repository**
```bash
git clone https://github.com/yourusername/hitmanki-store.git
cd hitmanki-store
```

2. **Setup environment**
```bash
cp .env.example .env
```

3. **Start with Docker**
```bash
docker-compose up -d
```

4. **Install dependencies**
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

5. **Access the application**
- Frontend: http://localhost:3000
- Admin Panel: http://localhost:8000/admin
- WebSocket: ws://localhost:3001

## 🌟 Features

### Core Features
- ✅ Steam OpenID Authentication
- ✅ Real-time case opening animations
- ✅ Provably fair system
- ✅ Multi-case opening
- ✅ Auto-opening feature
- ✅ Live drop feed
- ✅ User inventory management
- ✅ Referral system
- ✅ Achievement/Level system

### Payment Integration
- ✅ Qiwi Wallet
- ✅ YooMoney
- ✅ Crypto Bot
- ✅ Bank Cards
- ✅ Steam Market API

### Admin Features
- ✅ User management
- ✅ Case & item management
- ✅ Drop rate configuration
- ✅ Financial analytics
- ✅ Promo code system
- ✅ Live monitoring
- ✅ Anti-fraud system

## 📄 License

This project is proprietary software. All rights reserved.

## 🤝 Support

For support, contact us at support@hitmanki.store or join our Telegram channel.