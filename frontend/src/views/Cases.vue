<template>
  <div class="cases py-16">
    <div class="container">
      <h1 class="text-4xl font-bold text-center mb-12">Кейсы</h1>
      
      <!-- Filters -->
      <div class="mb-8 flex flex-wrap gap-4 justify-center">
        <button class="btn-secondary">Все кейсы</button>
        <button class="btn-secondary">Популярные</button>
        <button class="btn-secondary">Новые</button>
        <button class="btn-secondary">Дешевые</button>
      </div>

      <!-- Cases Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="case_item in cases" :key="case_item.id" class="card hover:border-yellow-500 transition-colors">
          <img :src="case_item.image" :alt="case_item.name" class="w-full h-48 object-cover rounded-lg mb-4">
          <h3 class="text-xl font-semibold mb-2">{{ case_item.name }}</h3>
          <p class="text-gray-400 text-sm mb-4">{{ case_item.description }}</p>
          <div class="flex justify-between items-center">
            <span class="text-yellow-500 font-bold text-lg">₽{{ case_item.price }}</span>
            <button class="btn-primary">Открыть</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const cases = ref([
  { id: 1, name: 'AK-47 Case', description: 'Легендарные AK-47', price: 100, image: 'https://via.placeholder.com/300x200?text=AK-47' },
  { id: 2, name: 'Knife Case', description: 'Редкие ножи', price: 500, image: 'https://via.placeholder.com/300x200?text=Knife' },
  { id: 3, name: 'Glove Case', description: 'Эксклюзивные перчатки', price: 250, image: 'https://via.placeholder.com/300x200?text=Gloves' },
  { id: 4, name: 'AWP Case', description: 'Снайперские винтовки', price: 150, image: 'https://via.placeholder.com/300x200?text=AWP' },
  { id: 5, name: 'Pistol Case', description: 'Пистолеты', price: 75, image: 'https://via.placeholder.com/300x200?text=Pistol' },
  { id: 6, name: 'Rare Case', description: 'Самые редкие предметы', price: 1000, image: 'https://via.placeholder.com/300x200?text=Rare' }
])
</script>