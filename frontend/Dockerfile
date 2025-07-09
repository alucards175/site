# Multi-stage build
FROM node:18-alpine as build-stage

# Установка рабочей директории
WORKDIR /app

# Копирование файлов зависимостей
COPY package*.json ./

# Установка зависимостей
RUN npm ci

# Копирование исходного кода
COPY . .

# Сборка приложения для продакшена
RUN npm run build

# Production stage
FROM nginx:alpine as production-stage

# Установка зависимостей для nginx
RUN apk add --no-cache curl

# Копирование собранного приложения
COPY --from=build-stage /app/dist /usr/share/nginx/html

# Копирование конфигурации nginx
COPY nginx.conf /etc/nginx/conf.d/default.conf

# Открытие порта
EXPOSE 3000

# Проверка здоровья
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
  CMD curl -f http://localhost:3000 || exit 1

# Запуск nginx
CMD ["nginx", "-g", "daemon off;"]