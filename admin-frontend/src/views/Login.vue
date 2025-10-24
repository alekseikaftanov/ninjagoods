<template>
  <div class="login-container">
    <div class="login-card">
      <div class="login-header">
        <h1>Ninja Goods</h1>
        <p>Админ-панель</p>
      </div>
      
      <form @submit.prevent="handleLogin" class="login-form">
        <div class="form-group">
          <label class="form-label" for="email">Email</label>
          <input
            id="email"
            v-model="form.email"
            type="email"
            class="form-input"
            placeholder="admin@ninjagoods.com"
            required
          />
        </div>
        
        <div class="form-group">
          <label class="form-label" for="password">Пароль</label>
          <input
            id="password"
            v-model="form.password"
            type="password"
            class="form-input"
            placeholder="admin123"
            required
          />
        </div>
        
        <button type="submit" class="btn btn-primary" :disabled="loading">
          {{ loading ? 'Вход...' : 'Войти' }}
        </button>
      </form>
      
      <div v-if="error" class="error-message">
        {{ error }}
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const form = ref({
  email: '',
  password: ''
})

const loading = ref(false)
const error = ref('')

const handleLogin = async () => {
  loading.value = true
  error.value = ''
  
  try {
    await authStore.login(form.value.email, form.value.password)
    router.push('/dashboard')
  } catch (err: any) {
    error.value = err.message || 'Ошибка входа'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.login-container {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: var(--bg-primary);
}

.login-card {
  background: var(--bg-secondary);
  border-radius: var(--radius-lg);
  padding: var(--spacing-xl);
  box-shadow: 0 8px 32px var(--shadow-medium);
  width: 100%;
  max-width: 400px;
}

.login-header {
  text-align: center;
  margin-bottom: var(--spacing-xl);
}

.login-header h1 {
  color: var(--accent-blue);
  margin-bottom: var(--spacing-sm);
}

.login-form {
  margin-bottom: var(--spacing-lg);
}

.error-message {
  color: #FF3B30;
  font-size: var(--font-size-small);
  text-align: center;
  padding: var(--spacing-sm);
  background: rgba(255, 59, 48, 0.1);
  border-radius: var(--radius-sm);
}
</style>
