FROM node:18-alpine

# Установка рабочей директории
WORKDIR /app

# Установка системных зависимостей
RUN apk add --no-cache python3 make g++

# Копирование файлов package.json и package-lock.json
COPY package*.json ./

# Установка зависимостей
RUN npm ci --only=production

# Копирование исходного кода приложения
COPY . .

# Создание пользователя node
RUN addgroup -g 1001 -S nodejs
RUN adduser -S node -u 1001

# Изменение владельца файлов
RUN chown -R node:nodejs /app
USER node

# Открытие порта
EXPOSE 3001

# Проверка здоровья
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD node -e "require('http').get('http://localhost:3001/health', (res) => { process.exit(res.statusCode === 200 ? 0 : 1) })"

# Запуск приложения
CMD ["node", "server.js"]