import { createRouter, createWebHistory } from 'vue-router'
import Layout from '../components/Layout.vue'
import YandexReviews from '../components/YandexReviews.vue'
import YandexSettings from '../components/YandexSettings.vue'
import Login from '../views/Login.vue'
import { useAuthStore } from '../stores/auth'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { guest: true }
  },
  {
    path: '/',
    component: Layout,
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        redirect: '/settings'
      },
      {
        path: 'reviews',
        component: YandexReviews,
        name: 'reviews',
        meta: { requiresAuth: true }
      },
      {
        path: 'settings',
        component: YandexSettings,
        name: 'settings',
        meta: { requiresAuth: true }
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore()
  
  if (authStore.isAuthenticated && !authStore.user) {
    await authStore.fetchUser()
  }

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next('/login')
  } 
  else if (to.path === '/login' && authStore.isAuthenticated) {
    next('/settings')
  } 
  else {
    next()
  }
})

export default router