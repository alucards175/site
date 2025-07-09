# Hitmanki Store - Complete Case Opening Website

## 🏗️ Project Overview

A comprehensive case opening website built with modern web technologies, featuring real-time interactions, Steam integration, provably fair system, and complete e-commerce functionality for virtual items.

## 🛠️ Technology Stack

### Backend
- **Laravel 10** - Main API and admin panel
- **MySQL 8.0** - Primary database
- **Redis** - Caching, sessions, queues, and real-time data
- **Laravel Horizon** - Queue monitoring
- **Laravel Sanctum** - API authentication

### WebSocket Server
- **Node.js 18+** - Runtime environment
- **Socket.io** - Real-time communication
- **Express.js** - HTTP server framework
- **IORedis** - Redis client for Node.js

### Frontend
- **Vue.js 3** - Progressive framework
- **Vite** - Build tool and dev server
- **TailwindCSS** - Utility-first CSS framework
- **Pinia** - State management
- **Socket.io Client** - Real-time communication

### Infrastructure
- **Docker & Docker Compose** - Containerization
- **Nginx** - Reverse proxy and static files
- **Let's Encrypt** - SSL certificates

## 📁 Complete Project Structure

```
hitmanki-store/
├── 📁 backend/                    # Laravel API & Admin
│   ├── 📁 app/
│   │   ├── 📁 Http/Controllers/Api/
│   │   │   ├── CaseController.php
│   │   │   ├── AuthController.php
│   │   │   ├── UserController.php
│   │   │   ├── InventoryController.php
│   │   │   ├── PaymentController.php
│   │   │   ├── PromoCodeController.php
│   │   │   ├── LiveFeedController.php
│   │   │   └── StatisticsController.php
│   │   ├── 📁 Models/
│   │   │   ├── User.php               # User with Steam integration
│   │   │   ├── CaseModel.php          # Cases with provably fair
│   │   │   ├── Item.php               # Virtual items
│   │   │   ├── CaseOpening.php        # Opening events
│   │   │   ├── UserInventory.php      # User items
│   │   │   ├── Deposit.php            # Payment deposits
│   │   │   ├── Withdrawal.php         # Payment withdrawals
│   │   │   ├── PromoCode.php          # Promo codes
│   │   │   └── Achievement.php        # User achievements
│   │   ├── 📁 Services/
│   │   │   ├── CaseOpeningService.php # Core opening logic
│   │   │   ├── ProvablyFairService.php # Cryptographic fairness
│   │   │   ├── PaymentService.php     # Payment processing
│   │   │   ├── SteamService.php       # Steam API integration
│   │   │   └── TelegramService.php    # Telegram notifications
│   │   ├── 📁 Jobs/
│   │   │   ├── BroadcastCaseOpening.php
│   │   │   ├── ProcessDeposit.php
│   │   │   └── SendTelegramNotification.php
│   │   └── 📁 Events/
│   │       ├── CaseOpened.php
│   │       ├── UserRegistered.php
│   │       └── BigWin.php
│   ├── 📁 database/
│   │   ├── 📁 migrations/
│   │   │   ├── 2024_01_01_000001_create_users_table.php
│   │   │   ├── 2024_01_01_000002_create_cases_table.php
│   │   │   ├── 2024_01_01_000003_create_items_table.php
│   │   │   ├── 2024_01_01_000004_create_case_items_table.php
│   │   │   ├── 2024_01_01_000005_create_case_openings_table.php
│   │   │   └── 2024_01_01_000006_create_user_inventories_table.php
│   │   ├── 📁 seeders/
│   │   │   ├── DatabaseSeeder.php
│   │   │   ├── CaseSeeder.php
│   │   │   ├── ItemSeeder.php
│   │   │   └── UserSeeder.php
│   │   └── 📁 factories/
│   ├── 📁 routes/
│   │   ├── api.php                    # Comprehensive API routes
│   │   └── web.php
│   ├── 📁 config/
│   │   ├── app.php                    # Main configuration
│   │   ├── database.php               # Database config
│   │   ├── steam.php                  # Steam integration
│   │   └── payments.php               # Payment gateways
│   ├── composer.json                  # PHP dependencies
│   ├── Dockerfile                     # Laravel container
│   └── artisan                        # Laravel CLI
│
├── 📁 websocket/                  # Node.js WebSocket Server
│   ├── server.js                      # Main server file
│   ├── package.json                   # Node.js dependencies
│   ├── Dockerfile                     # Node.js container
│   └── 📁 src/
│       ├── 📁 handlers/
│       ├── 📁 middleware/
│       └── 📁 utils/
│
├── 📁 frontend/                   # Vue.js SPA
│   ├── 📁 src/
│   │   ├── 📁 components/
│   │   │   ├── 📁 case/
│   │   │   │   ├── CaseCard.vue
│   │   │   │   ├── CaseOpening.vue
│   │   │   │   ├── CaseAnimation.vue
│   │   │   │   └── CaseDetails.vue
│   │   │   ├── 📁 ui/
│   │   │   │   ├── Modal.vue
│   │   │   │   ├── Button.vue
│   │   │   │   ├── Loading.vue
│   │   │   │   └── Toast.vue
│   │   │   ├── 📁 auth/
│   │   │   │   ├── SteamLogin.vue
│   │   │   │   └── UserProfile.vue
│   │   │   └── 📁 layout/
│   │   │       ├── Header.vue
│   │   │       ├── Sidebar.vue
│   │   │       └── Footer.vue
│   │   ├── 📁 views/
│   │   │   ├── Home.vue
│   │   │   ├── Cases.vue
│   │   │   ├── CaseDetails.vue
│   │   │   ├── Inventory.vue
│   │   │   ├── Profile.vue
│   │   │   ├── Deposit.vue
│   │   │   ├── Withdraw.vue
│   │   │   └── About.vue
│   │   ├── 📁 stores/
│   │   │   ├── auth.ts
│   │   │   ├── cases.ts
│   │   │   ├── inventory.ts
│   │   │   ├── websocket.ts
│   │   │   └── payments.ts
│   │   ├── 📁 composables/
│   │   │   ├── useWebSocket.ts
│   │   │   ├── useAuth.ts
│   │   │   ├── useCases.ts
│   │   │   └── usePayments.ts
│   │   ├── 📁 utils/
│   │   │   ├── api.ts
│   │   │   ├── websocket.ts
│   │   │   ├── formatters.ts
│   │   │   └── validators.ts
│   │   ├── App.vue
│   │   └── main.ts
│   ├── package.json                   # Frontend dependencies
│   ├── vite.config.ts                 # Vite configuration
│   ├── tailwind.config.js             # TailwindCSS config
│   ├── Dockerfile                     # Frontend container
│   └── index.html
│
├── 📁 nginx/                      # Nginx Configuration
│   ├── nginx.conf                     # Main nginx config
│   ├── 📁 sites-available/
│   │   └── hitmanki.store.conf        # Site configuration
│   └── 📁 ssl/                       # SSL certificates
│
├── 📁 docs/                       # Documentation
│   ├── PROJECT_STRUCTURE.md          # This file
│   ├── API_DOCUMENTATION.md
│   ├── DEPLOYMENT.md
│   ├── SECURITY.md
│   └── CONTRIBUTING.md
│
├── docker-compose.yml                 # Complete Docker setup
├── .env.example                       # Environment template
├── .gitignore                         # Git ignore rules
└── README.md                          # Project overview
```

## 🔧 Key Features Implemented

### 🎰 Case Opening System
- **Provably Fair Algorithm** - Cryptographically secure randomization
- **Multi-case Opening** - Open multiple cases simultaneously
- **Auto-opening Mode** - Automated continuous opening
- **Real-time Animations** - Smooth GSAP-powered animations
- **Live Drop Feed** - Real-time display of all openings

### 👤 User Management
- **Steam OpenID Integration** - Secure Steam authentication
- **User Levels & Experience** - Progressive leveling system
- **Referral System** - Multi-tier referral bonuses
- **Achievement System** - Unlockable achievements
- **Anti-fraud Protection** - Multiple security measures

### 💰 Payment System
- **Multiple Gateways** - Qiwi, YooMoney, Crypto Bot, Bank Cards
- **Steam Market Integration** - Real-time price updates
- **Automatic Processing** - Webhook-based confirmations
- **Commission System** - Flexible fee structure

### 📦 Inventory Management
- **Real-time Updates** - Instant inventory updates
- **Item Withdrawal** - Steam trade integration
- **Item Selling** - Convert items to balance
- **Trade History** - Complete transaction logs

### 🔴 Real-time Features
- **Live Case Openings** - Real-time opening feed
- **Global Chat** - Multi-room chat system
- **Online Users** - Live user count and status
- **Notifications** - Real-time user notifications

### 🛡️ Security Features
- **Rate Limiting** - Multiple rate limiting layers
- **Input Validation** - Comprehensive validation
- **SQL Injection Protection** - Prepared statements
- **XSS Protection** - Content sanitization
- **CSRF Protection** - Token-based protection

### 📊 Analytics & Monitoring
- **Real-time Statistics** - Live site statistics
- **User Analytics** - Detailed user behavior
- **Financial Reports** - Revenue and profit tracking
- **System Monitoring** - Health checks and metrics

## 🗄️ Database Schema

### Core Tables
- **users** - User accounts with Steam integration
- **cases** - Available cases with pricing
- **items** - Virtual items with rarity system
- **case_items** - Case-item relationships with drop rates
- **case_openings** - Opening events with provably fair data
- **user_inventories** - User-owned items

### Financial Tables
- **deposits** - Payment deposits
- **withdrawals** - Payment withdrawals
- **transactions** - All financial transactions
- **promo_codes** - Promotional codes

### System Tables
- **achievements** - Available achievements
- **user_achievements** - User progress
- **user_sessions** - Active sessions
- **activity_logs** - Audit trail

## 🚀 API Endpoints

### Authentication
- `POST /api/v1/auth/steam` - Steam login
- `GET /api/v1/auth/user` - Get authenticated user
- `POST /api/v1/auth/logout` - Logout

### Cases
- `GET /api/v1/cases` - List cases
- `GET /api/v1/cases/{id}` - Case details
- `POST /api/v1/cases/{id}/open` - Open case
- `POST /api/v1/cases/{id}/multi-open` - Multi-open

### Inventory
- `GET /api/v1/inventory` - User inventory
- `POST /api/v1/inventory/{id}/withdraw` - Withdraw item
- `POST /api/v1/inventory/{id}/sell` - Sell item

### Payments
- `POST /api/v1/payments/deposit` - Create deposit
- `GET /api/v1/payments/deposits` - Deposit history
- `POST /api/v1/payments/withdraw` - Create withdrawal

## 🌐 WebSocket Events

### Client Events
- `join-room` - Join a room
- `subscribe-case` - Subscribe to case openings
- `send-message` - Send chat message
- `ping` - Connection test

### Server Events
- `case-opened` - New case opening
- `balance-updated` - User balance change
- `new-message` - New chat message
- `stats-updated` - Global statistics update

## 🔒 Security Measures

### Authentication & Authorization
- Steam OpenID integration
- JWT token-based API authentication
- Role-based access control
- Session management

### Anti-fraud Protection
- Rate limiting on all endpoints
- IP-based restrictions
- Behavioral analysis
- Automated suspicious activity detection

### Data Protection
- Input sanitization and validation
- SQL injection prevention
- XSS protection
- CSRF token validation

## 📈 Performance Optimizations

### Caching Strategy
- Redis for session storage
- Database query caching
- API response caching
- Static asset optimization

### Real-time Optimization
- WebSocket connection pooling
- Redis pub/sub for scaling
- Efficient data serialization
- Connection cleanup

### Database Optimization
- Proper indexing strategy
- Query optimization
- Connection pooling
- Read replicas for scaling

## 🛠️ Development Setup

### Prerequisites
- Docker & Docker Compose
- Node.js 18+
- PHP 8.1+
- Composer

### Quick Start
```bash
# Clone repository
git clone https://github.com/yourusername/hitmanki-store.git
cd hitmanki-store

# Setup environment
cp .env.example .env

# Start with Docker
docker-compose up -d

# Install dependencies
cd backend && composer install
cd ../frontend && npm install
cd ../websocket && npm install

# Run migrations
php artisan migrate --seed
```

## 📊 Monitoring & Analytics

### Health Checks
- Application health endpoints
- Database connection monitoring
- Redis connectivity checks
- WebSocket server status

### Logging
- Structured logging with context
- Error tracking and alerting
- Performance metrics
- User activity logs

### Analytics
- Real-time user metrics
- Financial transaction tracking
- Case opening statistics
- Performance monitoring

## 🚀 Deployment

### Production Environment
- Docker Swarm or Kubernetes
- Load balancing with Nginx
- SSL termination
- Database clustering

### CI/CD Pipeline
- GitHub Actions workflows
- Automated testing
- Docker image building
- Rolling deployments

### Backup Strategy
- Database backups
- File storage backups
- Configuration backups
- Disaster recovery procedures

## 📝 Additional Documentation

- **API Documentation** - Complete API reference
- **Deployment Guide** - Production deployment instructions
- **Security Guidelines** - Security best practices
- **Contributing Guide** - Development guidelines

## 🎯 Future Enhancements

### Planned Features
- Mobile application (React Native)
- Advanced trading system
- Tournament mode
- VIP membership system
- Multi-language support

### Technical Improvements
- Microservices architecture
- Advanced analytics
- Machine learning integration
- Enhanced security measures

---

**Note**: This is a comprehensive case opening website with all essential features for a production-ready gambling platform. All code follows best practices and industry standards for security, performance, and maintainability.