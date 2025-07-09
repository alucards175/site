<template>
  <div class="inventory py-16">
    <div class="container">
      <h1 class="text-4xl font-bold text-center mb-12">Инвентарь</h1>
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
        <div v-for="item in inventoryItems" :key="item.id" class="card p-3">
          <img :src="item.image" :alt="item.name" class="w-full h-20 object-cover rounded mb-2">
          <h4 class="text-sm font-medium mb-1">{{ item.name }}</h4>
          <p class="text-yellow-500 text-xs">₽{{ item.price }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const inventoryItems = ref([
  { id: 1, name: 'AK-47 Redline', price: 2500, image: 'https://via.placeholder.com/80x80?text=AK' },
  { id: 2, name: 'AWP Dragon Lore', price: 25000, image: 'https://via.placeholder.com/80x80?text=AWP' },
  { id: 3, name: 'Karambit Fade', price: 45000, image: 'https://via.placeholder.com/80x80?text=Knife' }
])
</script>