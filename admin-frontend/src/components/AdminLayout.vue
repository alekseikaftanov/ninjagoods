<template>
  <div class="admin-layout">
    <aside class="sidebar">
      <div class="sidebar-content">
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
              <router-link to="/orders" class="nav-item" :class="{ active: $route.path === '/orders' }">
                📦 Заказы
              </router-link>
        </nav>
      </div>
      
      <div class="sidebar-footer">
        <button @click="handleLogout" class="logout-btn">
          <span class="logout-icon">🚪</span>
          <span class="logout-text">Выйти</span>
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
.admin-layout {
  display: flex;
  min-height: 100vh;
  width: 100%;
}

.sidebar {
  width: 280px;
  background: rgba(255, 255, 255, 0.8);
  backdrop-filter: blur(20px);
  border-right: 1px solid var(--border-color);
  display: flex;
  flex-direction: column;
  position: fixed;
  top: 0;
  left: 0;
  height: 100vh;
  z-index: 1000;
}

.sidebar-content {
  flex: 1;
  padding: var(--spacing-xl);
  overflow-y: auto;
  display: flex;
  flex-direction: column;
}

.sidebar-header {
  margin-bottom: var(--spacing-xl);
  padding-bottom: var(--spacing-lg);
  border-bottom: 1px solid var(--border-color);
}

.sidebar-header h2 {
  color: var(--accent-blue);
  margin-bottom: var(--spacing-xs);
  font-size: var(--font-size-medium);
  font-weight: 600;
}

.sidebar-header p {
  font-size: var(--font-size-small);
  color: var(--text-secondary);
  margin: 0;
}

.sidebar-nav {
  flex: 1;
  margin-bottom: var(--spacing-xl);
}

.nav-item {
  display: block;
  padding: var(--spacing-md) var(--spacing-lg);
  color: var(--text-primary);
  text-decoration: none;
  border-radius: var(--radius-sm);
  margin-bottom: var(--spacing-xs);
  transition: all 0.2s ease;
  font-weight: 500;
}

.nav-item:hover {
  background-color: var(--accent-blue-light);
  color: var(--accent-blue);
}

.nav-item.active {
  background-color: var(--accent-blue);
  color: white;
  box-shadow: 0 2px 8px rgba(0, 122, 255, 0.3);
}

.sidebar-footer {
  padding: var(--spacing-lg) var(--spacing-xl);
  border-top: 1px solid var(--border-color);
  background: rgba(255, 255, 255, 0.9);
  backdrop-filter: blur(10px);
}

.logout-btn {
  width: 100%;
  background: #FF3B30;
  color: white;
  border: none;
  border-radius: var(--radius-md);
  padding: var(--spacing-md) var(--spacing-lg);
  font-size: var(--font-size-regular);
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: var(--spacing-sm);
  box-shadow: 0 2px 8px rgba(255, 59, 48, 0.3);
}

.logout-btn:hover {
  background: #D70015;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(255, 59, 48, 0.4);
}

.logout-btn:active {
  transform: translateY(0);
  box-shadow: 0 2px 6px rgba(255, 59, 48, 0.3);
}

.logout-icon {
  font-size: 16px;
}

.logout-text {
  font-weight: 600;
}

.main-content {
  flex: 1;
  padding: var(--spacing-xl);
  margin-left: 280px;
  max-width: calc(100% - 280px);
  min-height: 100vh;
}

/* Адаптивность */
@media (max-width: 768px) {
  .sidebar {
    width: 100%;
    height: auto;
    position: relative;
    border-right: none;
    border-bottom: 1px solid var(--border-color);
  }
  
  .main-content {
    margin-left: 0;
    max-width: 100%;
  }
  
  .sidebar-content {
    padding: var(--spacing-lg);
  }
  
  .sidebar-footer {
    padding: var(--spacing-md) var(--spacing-lg);
  }
}
</style>
