import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import axios from 'axios'

export const useAuthStore = defineStore('auth', () => {
    const user = ref(null)
    const loading = ref(false)

    const isAuthenticated = computed(() => !!user.value)

    axios.defaults.withCredentials = true

    // Login
    async function login(credentials) {
        loading.value = true
        try {
            await axios.get('/sanctum/csrf-cookie');

            const response = await axios.post('/login', credentials)
            
            user.value = response.data.user
            return { success: true }
        } catch (error) {
            console.error('Login error:', error)
            return { 
                success: false, 
                message: error.response?.data?.message || 'Ошибка входа' 
            }
        } finally {
            loading.value = false
        }
    }

    // Logout
    async function logout() {
        loading.value = true
        try {
            await axios.post('/logout')
            user.value = null
            return { success: true }
        } catch (error) {
            console.error('Logout error:', error)
            user.value = null
            return { success: false }
        } finally {
            loading.value = false
        }
    }

    // Получить данные пользователя
    async function fetchUser() {
        try {
            const response = await axios.get('/user')
            user.value = response.data
            return response.data
        } catch (error) {
            user.value = null
            return null
        }
    }

    return {
        user,
        loading,
        isAuthenticated,
        login,
        logout,
        fetchUser
    }
})