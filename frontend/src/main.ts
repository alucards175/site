import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { createRouter, createWebHistory } from 'vue-router'
import App from './App.vue'
import './style.css'

// Import components for routing
import Home from './views/Home.vue'
import Cases from './views/Cases.vue'
import CaseDetails from './views/CaseDetails.vue'
import Profile from './views/Profile.vue'
import Inventory from './views/Inventory.vue'
import Deposit from './views/Deposit.vue'
import Withdraw from './views/Withdraw.vue'
import About from './views/About.vue'

// Create router
const routes = [
  { path: '/', name: 'home', component: Home },
  { path: '/cases', name: 'cases', component: Cases },
  { path: '/cases/:id', name: 'case-details', component: CaseDetails },
  { path: '/profile', name: 'profile', component: Profile },
  { path: '/inventory', name: 'inventory', component: Inventory },
  { path: '/deposit', name: 'deposit', component: Deposit },
  { path: '/withdraw', name: 'withdraw', component: Withdraw },
  { path: '/about', name: 'about', component: About },
  // Redirect unknown routes to home
  { path: '/:pathMatch(.*)*', redirect: '/' }
]

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition
    } else {
      return { top: 0 }
    }
  }
})

// Create pinia store
const pinia = createPinia()

// Create and mount app
const app = createApp(App)

app.use(pinia)
app.use(router)

// Global error handler
app.config.errorHandler = (err, vm, info) => {
  console.error('Vue error:', err, info)
}

// Mount app
app.mount('#app')

// Remove loading screen
const loading = document.getElementById('loading')
if (loading) {
  setTimeout(() => {
    loading.style.opacity = '0'
    setTimeout(() => {
      loading.remove()
    }, 300)
  }, 500)
}