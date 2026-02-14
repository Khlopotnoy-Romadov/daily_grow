import './bootstrap';
import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'

console.log('Starting Vue app...');

const app = createApp(App)

app.use(createPinia())
app.use(router)

app.mount('#app')

console.log('Vue app mounted');