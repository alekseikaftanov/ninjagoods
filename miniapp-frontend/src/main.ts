import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import App from './App.vue'
import { useB2BAuthStore } from './stores/b2bAuth'

const app = createApp(App)

const pinia = createPinia()
app.use(pinia)
app.use(router)

// Initialize B2B auth from storage
const b2bAuthStore = useB2BAuthStore()
b2bAuthStore.initFromStorage()

app.mount('#app')
