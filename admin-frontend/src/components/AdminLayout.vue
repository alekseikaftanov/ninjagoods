<template>
  <div class="admin-layout">
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2>Ninja Goods</h2>
        <p>Админ-панель</p>
      </div>
      
      <nav class="sidebar-nav">
        <router-link to="/dashboard" class="nav-item" :class="{ active: $route.path === '/dashboard' }">
          📊 Дашборд
        </router-link>
        <router-link to="/categories" class="nav-item" :class="{ active: $route.path === '/categories' }">
          📁 Категории
        </router-link>
        <router-link to="/products" class="nav-item" :class="{ active: $route.path === '/products' }">
          🛍️ Товары
        </router-link>
      </nav>
      
      <div class="sidebar-footer">
        <button @click="handleLogout" class="btn btn-secondary">
          Выйти
        </button>
      </div>
    </aside>
    
    <main class="main-content">
      <slot />
    </main>
  </div>
</template>

<script setup lang="ts">
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const handleLogout = () => {
  authStore.logout()
  router.push('/login')
}
</script>

<style scoped>
.sidebar-header {
  margin-bottom: var(--spacing-xl);
  padding-bottom: var(--spacing-lg);
  border-bottom: 1px solid var(--border-color);
}

.sidebar-header h2 {
  color: var(--accent-blue);
  margin-bottom: var(--spacing-xs);
}

.sidebar-header p {
  font-size: var(--font-size-small);
  margin: 0;
}

.sidebar-nav {
  flex: 1;
  margin-bottom: var(--spacing-xl);
}

.sidebar-footer {
  padding-top: var(--spacing-lg);
  border-top: 1px solid var(--border-color);
}
</style>
