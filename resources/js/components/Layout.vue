<template>
  <div class="layout">
    <!-- Боковое меню (левая панель) -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h1 class="app-title">Daily Grow</h1>
        <p class="app-subtitle">Панель управления</p>
      </div>
      
      <div class="user-info">
        <div class="user-avatar">
          <span class="avatar-text">{{ userInitials }}</span>
        </div>
        <div class="user-details">
          <p class="user-label">Пользователь</p>
          <p class="user-name">{{ authStore.user?.login || 'Загрузка...' }}</p>
        </div>
      </div>
      
      <div class="nav-section">
        <p class="nav-section-title">Навигация</p>
        <nav class="nav-menu">
          <router-link 
            to="/reviews" 
            class="nav-link"
            :class="{ 'nav-link-active': $route.path === '/reviews' }"
          >
            <span class="nav-icon">📊</span>
            <span>Отзывы</span>
            <span v-if="$route.path === '/reviews'" class="nav-badge"></span>
          </router-link>
          
          <router-link 
            to="/settings" 
            class="nav-link"
            :class="{ 'nav-link-active': $route.path === '/settings' }"
          >
            <span class="nav-icon">⚙️</span>
            <span>Настройки</span>
            <span v-if="$route.path === '/settings'" class="nav-badge"></span>
          </router-link>
        </nav>
      </div>
      
      <div class="sidebar-footer">
        <button 
          @click="handleLogout" 
          class="logout-btn"
          :disabled="authStore.loading"
        >
          <span class="logout-icon">👋</span>
          <span>{{ authStore.loading ? 'Выход...' : 'Выйти' }}</span>
        </button>
        <p class="version-text">Версия 1.0.0</p>
      </div>
    </aside>

    <!-- Основной контент -->
    <main class="main-content">
      <div class="content-wrapper">
        <router-view></router-view>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

// Инициалы пользователя для аватара
const userInitials = computed(() => {
  if (authStore.user?.login) {
    return authStore.user.login.substring(0, 2).toUpperCase()
  }
  return 'DG'
})

// Выход из системы
const handleLogout = async () => {
  const result = await authStore.logout()
  if (result.success) {
    router.push('/login')
  }
}
</script>

<style src="../../css/layout.css" scoped></style>