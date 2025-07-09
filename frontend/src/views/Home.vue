<template>
  <div class="home">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-gray-800 to-gray-900 py-20">
      <div class="container">
        <div class="text-center max-w-4xl mx-auto">
          <h1 class="text-5xl md:text-6xl font-bold mb-6 animate-fade-in">
            Открывай кейсы 
            <span class="text-yellow-500">честно</span>
          </h1>
          <p class="text-xl text-gray-300 mb-8 animate-slide-up">
            Лучший сайт для открытия кейсов CS:GO с системой Provably Fair, 
            интеграцией Steam и моментальным выводом скинов
          </p>
          <div class="flex flex-col sm:flex-row gap-4 justify-center animate-slide-up">
            <router-link to="/cases" class="btn-primary">
              Открыть кейс
            </router-link>
            <button class="btn-secondary">
              Войти через Steam
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- Stats Section -->
    <section class="py-16 bg-gray-800">
      <div class="container">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
          <div class="animate-fade-in">
            <div class="text-3xl font-bold text-yellow-500 mb-2">1,234,567</div>
            <div class="text-gray-400">Открыто кейсов</div>
          </div>
          <div class="animate-fade-in">
            <div class="text-3xl font-bold text-yellow-500 mb-2">₽2,345,678</div>
            <div class="text-gray-400">Выплачено</div>
          </div>
          <div class="animate-fade-in">
            <div class="text-3xl font-bold text-yellow-500 mb-2">12,345</div>
            <div class="text-gray-400">Пользователей</div>
          </div>
          <div class="animate-fade-in">
            <div class="text-3xl font-bold text-yellow-500 mb-2">456</div>
            <div class="text-gray-400">Онлайн</div>
          </div>
        </div>
      </div>
    </section>

    <!-- Popular Cases -->
    <section class="py-16">
      <div class="container">
        <h2 class="text-3xl font-bold text-center mb-12">Популярные кейсы</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <div v-for="case_item in popularCases" :key="case_item.id" class="card hover:border-yellow-500 transition-colors cursor-pointer">
            <div class="aspect-w-16 aspect-h-9 mb-4">
              <img :src="case_item.image" :alt="case_item.name" class="w-full h-48 object-cover rounded-lg">
            </div>
            <h3 class="text-xl font-semibold mb-2">{{ case_item.name }}</h3>
            <p class="text-gray-400 text-sm mb-4">{{ case_item.description }}</p>
            <div class="flex justify-between items-center">
              <span class="text-yellow-500 font-bold">₽{{ case_item.price }}</span>
              <button class="btn-primary text-sm">Открыть</button>
            </div>
          </div>
        </div>
        <div class="text-center mt-12">
          <router-link to="/cases" class="btn-secondary">
            Все кейсы
          </router-link>
        </div>
      </div>
    </section>

    <!-- Features -->
    <section class="py-16 bg-gray-800">
      <div class="container">
        <h2 class="text-3xl font-bold text-center mb-12">Почему выбирают нас?</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
          <div class="text-center">
            <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl">🎯</span>
            </div>
            <h3 class="text-xl font-semibold mb-4">Provably Fair</h3>
            <p class="text-gray-400">Каждое открытие можно проверить на честность</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl">⚡</span>
            </div>
            <h3 class="text-xl font-semibold mb-4">Мгновенный вывод</h3>
            <p class="text-gray-400">Выводите скины на Steam без задержек</p>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl">🛡️</span>
            </div>
            <h3 class="text-xl font-semibold mb-4">Безопасность</h3>
            <p class="text-gray-400">Ваши данные и средства под надежной защитой</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Live Feed -->
    <section class="py-16">
      <div class="container">
        <h2 class="text-3xl font-bold text-center mb-12">Последние выигрыши</h2>
        <div class="space-y-4 max-w-4xl mx-auto">
          <div v-for="win in recentWins" :key="win.id" class="card">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-4">
                <img :src="win.user.avatar" :alt="win.user.name" class="w-10 h-10 rounded-full">
                <div>
                  <div class="font-semibold">{{ win.user.name }}</div>
                  <div class="text-sm text-gray-400">{{ win.case.name }}</div>
                </div>
              </div>
              <div class="flex items-center space-x-4">
                <img :src="win.item.image" :alt="win.item.name" class="w-12 h-12 rounded">
                <div class="text-right">
                  <div class="font-semibold">{{ win.item.name }}</div>
                  <div class="text-yellow-500 font-bold">₽{{ win.item.price }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

// Mock data
const popularCases = ref([
  {
    id: 1,
    name: 'AK-47 Case',
    description: 'Кейс с легендарными AK-47',
    price: 100,
    image: 'https://via.placeholder.com/300x200?text=AK-47+Case'
  },
  {
    id: 2,
    name: 'Knife Case',
    description: 'Шанс получить нож!',
    price: 500,
    image: 'https://via.placeholder.com/300x200?text=Knife+Case'
  },
  {
    id: 3,
    name: 'Glove Case',
    description: 'Редкие перчатки',
    price: 250,
    image: 'https://via.placeholder.com/300x200?text=Glove+Case'
  }
])

const recentWins = ref([
  {
    id: 1,
    user: {
      name: 'Player123',
      avatar: 'https://via.placeholder.com/40x40?text=P1'
    },
    case: {
      name: 'AK-47 Case'
    },
    item: {
      name: 'AK-47 Redline',
      price: 2500,
      image: 'https://via.placeholder.com/48x48?text=AK'
    }
  },
  {
    id: 2,
    user: {
      name: 'ProGamer',
      avatar: 'https://via.placeholder.com/40x40?text=P2'
    },
    case: {
      name: 'Knife Case'
    },
    item: {
      name: 'Karambit Fade',
      price: 45000,
      image: 'https://via.placeholder.com/48x48?text=K'
    }
  },
  {
    id: 3,
    user: {
      name: 'Lucky777',
      avatar: 'https://via.placeholder.com/40x40?text=L7'
    },
    case: {
      name: 'Glove Case'
    },
    item: {
      name: 'Sport Gloves',
      price: 8500,
      image: 'https://via.placeholder.com/48x48?text=G'
    }
  }
])
</script>