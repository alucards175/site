<template>
  <div id="app" class="min-h-screen bg-gray-900 text-white">
    <!-- Header -->
    <header class="bg-gray-800 border-b border-gray-700">
      <nav class="container mx-auto px-4 py-4">
        <div class="flex items-center justify-between">
          <!-- Logo -->
          <router-link to="/" class="flex items-center space-x-2">
            <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
              <span class="font-bold text-gray-900">H</span>
            </div>
            <span class="text-xl font-bold">Hitmanki Store</span>
          </router-link>

          <!-- Navigation -->
          <div class="hidden md:flex items-center space-x-6">
            <router-link to="/" class="hover:text-yellow-500 transition-colors">Главная</router-link>
            <router-link to="/cases" class="hover:text-yellow-500 transition-colors">Кейсы</router-link>
            <router-link to="/inventory" class="hover:text-yellow-500 transition-colors">Инвентарь</router-link>
            <router-link to="/deposit" class="hover:text-yellow-500 transition-colors">Пополнить</router-link>
            <router-link to="/about" class="hover:text-yellow-500 transition-colors">О нас</router-link>
          </div>

          <!-- User menu -->
          <div class="flex items-center space-x-4">
            <button class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 px-4 py-2 rounded-lg font-medium transition-colors">
              Войти через Steam
            </button>
          </div>
        </div>
      </nav>
    </header>

    <!-- Main content -->
    <main class="flex-1">
      <router-view />
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 border-t border-gray-700 mt-16">
      <div class="container mx-auto px-4 py-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
          <!-- Logo and description -->
          <div class="col-span-1 md:col-span-2">
            <div class="flex items-center space-x-2 mb-4">
              <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                <span class="font-bold text-gray-900">H</span>
              </div>
              <span class="text-xl font-bold">Hitmanki Store</span>
            </div>
            <p class="text-gray-400 max-w-md">
              Лучший сайт для открытия кейсов CS:GO с честной системой Provably Fair, 
              интеграцией Steam и выводом скинов.
            </p>
          </div>

          <!-- Links -->
          <div>
            <h3 class="font-semibold mb-4">Навигация</h3>
            <ul class="space-y-2 text-gray-400">
              <li><router-link to="/" class="hover:text-white transition-colors">Главная</router-link></li>
              <li><router-link to="/cases" class="hover:text-white transition-colors">Кейсы</router-link></li>
              <li><router-link to="/inventory" class="hover:text-white transition-colors">Инвентарь</router-link></li>
              <li><router-link to="/about" class="hover:text-white transition-colors">О нас</router-link></li>
            </ul>
          </div>

          <!-- Support -->
          <div>
            <h3 class="font-semibold mb-4">Поддержка</h3>
            <ul class="space-y-2 text-gray-400">
              <li><a href="mailto:support@hitmanki.store" class="hover:text-white transition-colors">Email</a></li>
              <li><a href="https://t.me/hitmanki_support" class="hover:text-white transition-colors">Telegram</a></li>
              <li><a href="https://discord.gg/hitmanki" class="hover:text-white transition-colors">Discord</a></li>
            </ul>
          </div>
        </div>

        <div class="border-t border-gray-700 mt-8 pt-8 flex flex-col md:flex-row justify-between items-center">
          <p class="text-gray-400 text-sm">
            © {{ currentYear }} Hitmanki Store. Все права защищены.
          </p>
          <div class="flex items-center space-x-4 mt-4 md:mt-0">
            <span class="text-sm text-gray-400">18+</span>
            <span class="text-sm text-gray-400">Provably Fair</span>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

const currentYear = computed(() => new Date().getFullYear())
</script>

<style scoped>
.router-link-active {
  @apply text-yellow-500;
}
</style>