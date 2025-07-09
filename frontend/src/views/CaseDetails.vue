<template>
  <div class="case-details py-16">
    <div class="container">
      <div class="max-w-4xl mx-auto">
        <h1 class="text-4xl font-bold text-center mb-8">AK-47 Case</h1>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Case Image -->
          <div class="card">
            <img src="https://via.placeholder.com/400x300?text=AK-47+Case" alt="Case" class="w-full rounded-lg mb-4">
            <div class="text-center">
              <div class="text-3xl font-bold text-yellow-500 mb-2">₽100</div>
              <button class="btn-primary w-full">Открыть кейс</button>
            </div>
          </div>

          <!-- Case Items -->
          <div class="card">
            <h3 class="text-xl font-semibold mb-4">Содержимое кейса</h3>
            <div class="space-y-3">
              <div v-for="item in caseItems" :key="item.id" class="flex items-center justify-between p-3 bg-gray-700 rounded-lg">
                <div class="flex items-center space-x-3">
                  <img :src="item.image" :alt="item.name" class="w-12 h-12 rounded">
                  <div>
                    <div class="font-medium">{{ item.name }}</div>
                    <div class="text-sm text-gray-400">{{ item.rarity }}</div>
                  </div>
                </div>
                <div class="text-yellow-500 font-bold">₽{{ item.price }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'

const caseItems = ref([
  { id: 1, name: 'AK-47 Redline', rarity: 'Rare', price: 2500, image: 'https://via.placeholder.com/48x48?text=AK1' },
  { id: 2, name: 'AK-47 Vulcan', rarity: 'Epic', price: 5000, image: 'https://via.placeholder.com/48x48?text=AK2' },
  { id: 3, name: 'AK-47 Fire Serpent', rarity: 'Legendary', price: 15000, image: 'https://via.placeholder.com/48x48?text=AK3' }
])
</script>