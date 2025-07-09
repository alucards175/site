const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const Redis = require('ioredis');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const jwt = require('jsonwebtoken');
const axios = require('axios');
require('dotenv').config();

// Initialize Express app
const app = express();
const server = http.createServer(app);

// Redis clients
const redis = new Redis({
  host: process.env.REDIS_HOST || 'localhost',
  port: process.env.REDIS_PORT || 6379,
  password: process.env.REDIS_PASSWORD || null,
  db: process.env.REDIS_WS_DB || 3,
  retryDelayOnFailover: 100,
  maxRetriesPerRequest: 3,
});

const pubClient = redis.duplicate();
const subClient = redis.duplicate();

// Configure Socket.IO
const io = socketIo(server, {
  cors: {
    origin: process.env.FRONTEND_URL || "http://localhost:3000",
    methods: ["GET", "POST"],
    allowedHeaders: ["Authorization"],
    credentials: true
  },
  adapter: require('socket.io-redis')({
    pubClient,
    subClient
  })
});

// Security middleware
app.use(helmet({
  crossOriginEmbedderPolicy: false,
  contentSecurityPolicy: false,
}));

app.use(cors({
  origin: process.env.FRONTEND_URL || "http://localhost:3000",
  credentials: true
}));

// Rate limiting
const limiter = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  max: 100, // limit each IP to 100 requests per windowMs
  message: 'Too many requests from this IP'
});

app.use(limiter);
app.use(express.json());

// Health check endpoint
app.get('/health', (req, res) => {
  res.json({
    status: 'healthy',
    timestamp: new Date().toISOString(),
    uptime: process.uptime(),
    connections: io.engine.clientsCount
  });
});

// Authentication middleware for Socket.IO
io.use(async (socket, next) => {
  try {
    const token = socket.handshake.auth.token;
    
    if (!token) {
      // Allow anonymous connections for public data
      socket.isAuthenticated = false;
      socket.userId = null;
      return next();
    }

    // Verify token with Laravel backend
    const response = await axios.get(`${process.env.BACKEND_URL}/api/v1/auth/user`, {
      headers: {
        'Authorization': `Bearer ${token}`,
        'Accept': 'application/json'
      }
    });

    if (response.data.success) {
      socket.isAuthenticated = true;
      socket.userId = response.data.data.id;
      socket.user = response.data.data;
    } else {
      socket.isAuthenticated = false;
      socket.userId = null;
    }

    next();
  } catch (error) {
    console.error('Authentication error:', error.message);
    socket.isAuthenticated = false;
    socket.userId = null;
    next(); // Allow connection but mark as unauthenticated
  }
});

// Store active connections
const activeConnections = new Map();
const userSockets = new Map(); // userId -> Set of socket IDs

// Connection handling
io.on('connection', (socket) => {
  console.log(`Client connected: ${socket.id}`);
  
  activeConnections.set(socket.id, {
    userId: socket.userId,
    connectedAt: new Date(),
    isAuthenticated: socket.isAuthenticated
  });

  // Track user sockets for authenticated users
  if (socket.isAuthenticated && socket.userId) {
    if (!userSockets.has(socket.userId)) {
      userSockets.set(socket.userId, new Set());
    }
    userSockets.get(socket.userId).add(socket.id);
    
    // Join user-specific room
    socket.join(`user:${socket.userId}`);
    
    // Update user online status
    updateUserOnlineStatus(socket.userId, true);
  }

  // Join public rooms
  socket.join('live-feed');
  socket.join('global-stats');

  // Send initial data
  sendInitialData(socket);

  // Handle room joining
  socket.on('join-room', (room) => {
    const allowedRooms = [
      'live-feed',
      'big-wins',
      'case-openings',
      'global-chat',
      'notifications'
    ];

    if (allowedRooms.includes(room)) {
      socket.join(room);
      console.log(`Socket ${socket.id} joined room: ${room}`);
    }
  });

  // Handle room leaving
  socket.on('leave-room', (room) => {
    socket.leave(room);
    console.log(`Socket ${socket.id} left room: ${room}`);
  });

  // Handle case opening subscription
  socket.on('subscribe-case', (caseId) => {
    if (typeof caseId === 'number' && caseId > 0) {
      socket.join(`case:${caseId}`);
      console.log(`Socket ${socket.id} subscribed to case: ${caseId}`);
    }
  });

  // Handle case opening unsubscription
  socket.on('unsubscribe-case', (caseId) => {
    socket.leave(`case:${caseId}`);
    console.log(`Socket ${socket.id} unsubscribed from case: ${caseId}`);
  });

  // Handle chat messages (authenticated users only)
  socket.on('send-message', async (data) => {
    if (!socket.isAuthenticated) {
      socket.emit('error', { message: 'Authentication required' });
      return;
    }

    try {
      const { room, message } = data;
      
      if (!message || message.trim().length === 0) {
        return;
      }

      if (message.length > 500) {
        socket.emit('error', { message: 'Message too long' });
        return;
      }

      // Rate limiting for chat
      const userKey = `chat_rate:${socket.userId}`;
      const messageCount = await redis.incr(userKey);
      
      if (messageCount === 1) {
        await redis.expire(userKey, 60); // 1 minute window
      }
      
      if (messageCount > 10) { // 10 messages per minute
        socket.emit('error', { message: 'Chat rate limit exceeded' });
        return;
      }

      const chatMessage = {
        id: generateId(),
        user: {
          id: socket.user.id,
          name: socket.user.name,
          avatar: socket.user.avatar,
          level: socket.user.level
        },
        message: message.trim(),
        timestamp: new Date().toISOString(),
        room: room || 'global-chat'
      };

      // Broadcast to room
      io.to(room || 'global-chat').emit('new-message', chatMessage);
      
      // Store message in Redis
      await redis.lpush(`chat:${room || 'global-chat'}`, JSON.stringify(chatMessage));
      await redis.ltrim(`chat:${room || 'global-chat'}`, 0, 99); // Keep last 100 messages

    } catch (error) {
      console.error('Chat error:', error);
      socket.emit('error', { message: 'Failed to send message' });
    }
  });

  // Handle ping for connection testing
  socket.on('ping', () => {
    socket.emit('pong', { timestamp: Date.now() });
  });

  // Handle disconnection
  socket.on('disconnect', (reason) => {
    console.log(`Client disconnected: ${socket.id}, reason: ${reason}`);
    
    // Clean up tracking
    activeConnections.delete(socket.id);
    
    if (socket.isAuthenticated && socket.userId) {
      const userSocketSet = userSockets.get(socket.userId);
      if (userSocketSet) {
        userSocketSet.delete(socket.id);
        if (userSocketSet.size === 0) {
          userSockets.delete(socket.userId);
          // Update user offline status
          updateUserOnlineStatus(socket.userId, false);
        }
      }
    }
  });
});

// Listen for Redis events from Laravel
subClient.subscribe('case-opened', 'user-balance-updated', 'global-stats-updated');

subClient.on('message', async (channel, message) => {
  try {
    const data = JSON.parse(message);
    
    switch (channel) {
      case 'case-opened':
        handleCaseOpened(data);
        break;
        
      case 'user-balance-updated':
        handleUserBalanceUpdated(data);
        break;
        
      case 'global-stats-updated':
        handleGlobalStatsUpdated(data);
        break;
    }
  } catch (error) {
    console.error(`Error processing Redis message from ${channel}:`, error);
  }
});

// Event handlers
function handleCaseOpened(data) {
  console.log('Case opened:', data);
  
  // Broadcast to live feed
  io.to('live-feed').emit('case-opened', {
    id: data.id,
    user: data.user,
    case: data.case,
    item: data.item,
    profit: data.profit,
    isJackpot: data.is_jackpot,
    timestamp: data.created_at
  });

  // Broadcast to case-specific room
  io.to(`case:${data.case.id}`).emit('case-opened', data);

  // If it's a big win, broadcast to big-wins room
  if (data.is_jackpot) {
    io.to('big-wins').emit('big-win', data);
  }

  // Notify user specifically
  if (data.user.id) {
    io.to(`user:${data.user.id}`).emit('your-case-opened', data);
  }
}

function handleUserBalanceUpdated(data) {
  console.log('User balance updated:', data);
  
  // Notify specific user
  io.to(`user:${data.user_id}`).emit('balance-updated', {
    balance: data.balance,
    change: data.change,
    reason: data.reason
  });
}

function handleGlobalStatsUpdated(data) {
  console.log('Global stats updated');
  
  // Broadcast to all clients in global-stats room
  io.to('global-stats').emit('stats-updated', data);
}

// Utility functions
async function sendInitialData(socket) {
  try {
    // Send recent case openings
    const recentOpenings = await redis.lrange('recent:openings', 0, 49);
    const openings = recentOpenings.map(opening => JSON.parse(opening));
    socket.emit('initial-data', {
      recentOpenings: openings,
      onlineUsers: getOnlineUserCount(),
      timestamp: new Date().toISOString()
    });

    // Send chat history if joining chat room
    const chatHistory = await redis.lrange('chat:global-chat', 0, 19);
    const messages = chatHistory.map(msg => JSON.parse(msg)).reverse();
    socket.emit('chat-history', messages);

  } catch (error) {
    console.error('Error sending initial data:', error);
  }
}

function getOnlineUserCount() {
  return userSockets.size;
}

async function updateUserOnlineStatus(userId, isOnline) {
  try {
    await redis.hset('users:online', userId, isOnline ? Date.now() : 0);
    
    // Broadcast online user count update
    io.to('global-stats').emit('online-users-updated', {
      count: getOnlineUserCount(),
      userId: userId,
      isOnline: isOnline
    });
  } catch (error) {
    console.error('Error updating user online status:', error);
  }
}

function generateId() {
  return Math.random().toString(36).substr(2, 9);
}

// Periodic cleanup and stats
setInterval(async () => {
  try {
    // Clean up old online status
    const onlineUsers = await redis.hgetall('users:online');
    const now = Date.now();
    const fiveMinutesAgo = now - (5 * 60 * 1000);
    
    for (const [userId, timestamp] of Object.entries(onlineUsers)) {
      if (parseInt(timestamp) < fiveMinutesAgo) {
        await redis.hdel('users:online', userId);
      }
    }

    // Broadcast connection stats
    io.to('global-stats').emit('connection-stats', {
      totalConnections: activeConnections.size,
      authenticatedConnections: Array.from(activeConnections.values())
        .filter(conn => conn.isAuthenticated).length,
      onlineUsers: getOnlineUserCount(),
      timestamp: new Date().toISOString()
    });

  } catch (error) {
    console.error('Error in periodic cleanup:', error);
  }
}, 30000); // Every 30 seconds

// Graceful shutdown
process.on('SIGTERM', async () => {
  console.log('Received SIGTERM, shutting down gracefully');
  
  // Close Redis connections
  await redis.quit();
  await pubClient.quit();
  await subClient.quit();
  
  // Close HTTP server
  server.close(() => {
    console.log('Server closed');
    process.exit(0);
  });
});

process.on('SIGINT', async () => {
  console.log('Received SIGINT, shutting down gracefully');
  
  // Close Redis connections
  await redis.quit();
  await pubClient.quit();
  await subClient.quit();
  
  // Close HTTP server
  server.close(() => {
    console.log('Server closed');
    process.exit(0);
  });
});

// Error handling
process.on('uncaughtException', (error) => {
  console.error('Uncaught Exception:', error);
  process.exit(1);
});

process.on('unhandledRejection', (reason, promise) => {
  console.error('Unhandled Rejection at:', promise, 'reason:', reason);
  process.exit(1);
});

// Start server
const PORT = process.env.WS_PORT || 3001;
server.listen(PORT, () => {
  console.log(`WebSocket server running on port ${PORT}`);
  console.log(`Environment: ${process.env.NODE_ENV || 'development'}`);
  console.log(`Frontend URL: ${process.env.FRONTEND_URL || 'http://localhost:3000'}`);
  console.log(`Backend URL: ${process.env.BACKEND_URL || 'http://localhost:8000'}`);
});