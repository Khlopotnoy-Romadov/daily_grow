<template>
  <div class="login-container">
    <div class="login-box">
      <h2>Вход в систему</h2>
      <form @submit.prevent="handleLogin">
        <div class="form-group">
          <label>Логин</label>
          <input 
            type="text" 
            v-model="form.login" 
            required
            :disabled="authStore.loading"
            placeholder="Введите логин"
            autofocus
          >
        </div>
        <div class="form-group">
          <label>Пароль</label>
          <input 
            type="password" 
            v-model="form.password" 
            required
            :disabled="authStore.loading"
            placeholder="Введите пароль"
          >
        </div>
        <div v-if="error" class="error-message">{{ error }}</div>
        <button type="submit" :disabled="authStore.loading">
          {{ authStore.loading ? 'Вход...' : 'Войти' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  login: '',
  password: ''
})
const error = ref('')

async function handleLogin() {
  error.value = ''
  const result = await authStore.login(form.value)
  
  if (result.success) {
    router.push('/settings')
  } else {
    error.value = result.message
  }
}
</script>

<style scoped>
.login-container {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.login-box {
  background: white;
  padding: 2rem;
  border-radius: 10px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.1);
  width: 100%;
  max-width: 400px;
}

h2 {
  text-align: center;
  margin-bottom: 2rem;
  color: #333;
  font-size: 1.8rem;
}

.form-group {
  margin-bottom: 1.5rem;
}

label {
  display: block;
  margin-bottom: 0.5rem;
  color: #555;
  font-weight: 500;
}

input {
  width: 100%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  border-radius: 5px;
  font-size: 1rem;
  transition: border-color 0.3s;
}

input:focus {
  outline: none;
  border-color: #667eea;
  box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
}

button {
  width: 100%;
  padding: 0.75rem;
  background: #667eea;
  color: white;
  border: none;
  border-radius: 5px;
  font-size: 1rem;
  font-weight: 500;
  cursor: pointer;
  transition: background 0.3s;
}

button:hover:not(:disabled) {
  background: #5a67d8;
}

button:disabled {
  background: #ccc;
  cursor: not-allowed;
}

.error-message {
  color: #e53e3e;
  margin-bottom: 1rem;
  text-align: center;
  font-size: 0.9rem;
  padding: 0.5rem;
  background: #fff5f5;
  border-radius: 5px;
}
</style>