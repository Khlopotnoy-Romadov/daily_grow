// resources/js/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import Layout from '../components/Layout.vue'
import YandexReviews from '../components/YandexReviews.vue'
import YandexSettings from '../components/YandexSettings.vue'

const routes = [
  {
    path: '/',
    component: Layout,
    children: [
      {
        path: '',
        redirect: '/settings' // По умолчанию открываем настройки
      },
      {
        path: 'reviews',
        component: YandexReviews,
        name: 'reviews'
      },
      {
        path: 'settings',
        component: YandexSettings,
        name: 'settings'
      }
    ]
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router